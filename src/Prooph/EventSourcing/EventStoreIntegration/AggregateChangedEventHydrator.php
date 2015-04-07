<?php
/*
 * This file is part of the prooph/event-sourcing.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 08.06.14 - 14:25
 */

namespace Prooph\EventSourcing\EventStoreIntegration;

use Assert\Assertion;
use Prooph\EventSourcing\AggregateChanged;
use Prooph\EventStore\Stream\EventId;
use Prooph\EventStore\Stream\EventName;
use Prooph\EventStore\Stream\StreamEvent;
use Rhumsaa\Uuid\Uuid;

/**
 * Class AggregateChangedEventHydrator
 *
 * @package Prooph\EventSourcing\Mapping
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class AggregateChangedEventHydrator implements EventHydratorInterface
{
    /**
     * @param AggregateChanged[] $aggregateChangedEvents
     * @return StreamEvent[]
     */
    public function toStreamEvents(array $aggregateChangedEvents)
    {
        Assertion::allIsInstanceOf($aggregateChangedEvents, 'Prooph\EventSourcing\AggregateChanged');

        $streamEvents = array();

        foreach ($aggregateChangedEvents as $aggregateChangedEvent) {
            $streamEvents[] = $this->translateToStreamEvent($aggregateChangedEvent);
        }

        return $streamEvents;
    }

    /**
     * @param StreamEvent[] $streamEvents
     * @return AggregateChanged[]
     */
    public function toAggregateChangedEvents(array $streamEvents)
    {
        $aggregateChangedEvents = array();

        foreach ($streamEvents as $streamEvent)
        {
            $aggregateChangedEvents[] = $this->translateToAggregateChangedEvent($streamEvent);
        }

        return $aggregateChangedEvents;
    }

    /**
     * @param AggregateChanged $aggregateChanged
     * @return StreamEvent
     */
    protected function translateToStreamEvent(AggregateChanged $aggregateChanged)
    {
        return new StreamEvent(
            new EventId($aggregateChanged->uuid()->toString()),
            new EventName(get_class($aggregateChanged)),
            array_merge($aggregateChanged->payload(), array('aggregate_id' => $aggregateChanged->aggregateId())),
            $aggregateChanged->version(),
            $aggregateChanged->occurredOn()
        );
    }

    /**
     * @param StreamEvent $streamEvent
     * @return AggregateChanged
     * @throws \RuntimeException if construction fails
     */
    protected function translateToAggregateChangedEvent(StreamEvent $streamEvent)
    {
        if (! class_exists($streamEvent->eventName()->toString())) {
            throw new \RuntimeException(
                sprintf(
                    'Event %s can not be constructed. EventName is no valid class name',
                    $streamEvent->eventName()->toString()
                )
            );
        }

        $eventClass = $streamEvent->eventName()->toString();

        $payload = $streamEvent->payload();

        $aggregateId = $payload['aggregate_id'];

        unset($payload['aggregate_id']);

        return $eventClass::reconstitute(
            $aggregateId,
            $payload,
            Uuid::fromString($streamEvent->eventId()->toString()),
            $streamEvent->occurredOn(),
            $streamEvent->version()
        );
    }
}
 