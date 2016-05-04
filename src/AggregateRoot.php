<?php
/*
 * This file is part of the prooph/event-sourcing package.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 06/06/14 - 20:14
 */

namespace Prooph\EventSourcing;

/**
 * AggregateRoot
 *
 * @author Alexander Miertsch <contact@prooph.de>
 *
 * @package Prooph\EventSourcing
 */
abstract class AggregateRoot
{
    /**
     * Current version
     *
     * @var int
     */
    protected $version = 0;

    /**
     * List of events that are not committed to the EventStore
     *
     * @var AggregateChanged[]
     */
    protected $recordedEvents = [];

    /**
     * @param \Iterator $historyEvents
     *
     * @throws \RuntimeException
     *
     * @return static
     */
    protected static function reconstituteFromHistory(\Iterator $historyEvents)
    {
        $instance = new static();
        $instance->replay($historyEvents);

        return $instance;
    }

    /**
     * We do not allow public access to __construct, this way we make sure that an aggregate root can only
     * be constructed by static factories
     */
    protected function __construct()
    {
    }

    /**
     * @return string representation of the unique identifier of the aggregate root
     */
    abstract protected function aggregateId();

    /**
     * Get pending events and reset stack
     *
     * @return AggregateChanged[]
     */
    protected function popRecordedEvents()
    {
        $pendingEvents = $this->recordedEvents;

        $this->recordedEvents = [];

        return $pendingEvents;
    }

    /**
     * Record an aggregate changed event
     *
     * @param AggregateChanged $event
     */
    protected function recordThat(AggregateChanged $event)
    {
        $this->version += 1;

        $this->recordedEvents[] = $event->withVersion($this->version);

        $this->apply($event);
    }

    /**
     * Replay past events
     *
     * @param \Iterator $historyEvents
     *
     * @throws \RuntimeException
     * @return void
     */
    protected function replay(\Iterator $historyEvents)
    {
        foreach ($historyEvents as $pastEvent) {
            /** @var AggregateChanged $pastEvent */
            $this->version = $pastEvent->version();

            $this->apply($pastEvent);
        }
    }

    /**
     * Apply given event
     *
     * @param AggregateChanged $e
     * @throws \RuntimeException
     */
    protected function apply(AggregateChanged $e)
    {
        $handler = $this->determineEventHandlerMethodFor($e);

        if (! method_exists($this, $handler)) {
            throw new \RuntimeException(sprintf(
                'Missing event handler method %s for aggregate root %s',
                $handler,
                get_class($this)
            ));
        }

        $this->{$handler}($e);
    }

    /**
     * Determine event name
     *
     * @param AggregateChanged $e
     *
     * @return string
     */
    protected function determineEventHandlerMethodFor(AggregateChanged $e)
    {
        return 'when' . implode(array_slice(explode('\\', get_class($e)), -1));
    }
}
