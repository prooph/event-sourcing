<?php
/*
 * This file is part of the prooph/event-sourcing package.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
     * @var float 
     */
    protected $version = 0;
    
    /**
     * List of events that are not committed to the EventStore
     * 
     * @var AggregateChanged[]
     */
    protected $recordedEvents = array();

    /**
     * @param AggregateChanged[] $historyEvents
     */
    protected static function reconstituteFromHistory(array $historyEvents)
    {
        $instance = new static();
        $instance->replay($historyEvents);
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
        
        $this->recordedEvents = array();
        
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

        $event->trackVersion($this->version);

        $this->recordedEvents[] = $event;

        $this->apply($event);
    }

    /**
     * Replay past events
     *
     * @param AggregateChanged[] $historyEvents
     *
     * @throws \RuntimeException
     * @return void
     */
    protected function replay(array $historyEvents)
    {
        foreach ($historyEvents as $pastEvent) {
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
                "Missing event handler method %s for aggregate root %s",
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
        return 'when' . join('', array_slice(explode('\\', get_class($e)), -1));
    }
}
