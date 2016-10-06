<?php
/**
 * This file is part of the prooph/event-sourcing.
 *  (c) 2014-2016 prooph software GmbH <contact@prooph.de>
 *  (c) 2015-2016 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

declare(strict_types=1);

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
     * @throws \RuntimeException
     */
    protected static function reconstituteFromHistory(\Iterator $historyEvents): self
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

    abstract protected function aggregateId(): string;

    /**
     * Get pending events and reset stack
     *
     * @return AggregateChanged[]
     */
    protected function popRecordedEvents(): array
    {
        $pendingEvents = $this->recordedEvents;

        $this->recordedEvents = [];

        return $pendingEvents;
    }

    /**
     * Record an aggregate changed event
     */
    protected function recordThat(AggregateChanged $event): void
    {
        $this->version += 1;

        $this->recordedEvents[] = $event->withVersion($this->version);

        $this->apply($event);
    }

    /**
     * Replay past events
     *
     * @throws \RuntimeException
     */
    protected function replay(\Iterator $historyEvents): void
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
     * @throws \RuntimeException
     */
    protected function apply(AggregateChanged $e): void
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

    protected function determineEventHandlerMethodFor(AggregateChanged $e): string
    {
        return 'when' . implode(array_slice(explode('\\', get_class($e)), -1));
    }
}
