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

use Prooph\EventSourcing\AggregateChangedEvent;
use Prooph\EventStore\Stream\EventId;
use Prooph\EventStore\Stream\EventName;
use Prooph\EventStore\Stream\StreamEvent;
use Prooph\EventStore\Stream\StreamId;
use Rhumsaa\Uuid\Uuid;
use Zend\EventManager\Event;
use Zend\EventManager\EventManager;

/**
 * Class AggregateChangedEventHydrator
 *
 * @package Prooph\EventSourcing\Mapping
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class AggregateChangedEventHydrator implements EventHydratorInterface
{
    /**
     * @var EventManager
     */
    protected $lifeCycleEvents;


    /**
     * @param AggregateChangedEvent[] $aggregateChangedEvents
     * @return StreamEvent[]
     */
    public function toStreamEvents(array $aggregateChangedEvents)
    {
        \Assert\that($aggregateChangedEvents)->all()->isInstanceOf('Prooph\EventSourcing\AggregateChangedEvent');
        $streamEvents = array();

        foreach ($aggregateChangedEvents as $aggregateChangedEvent) {
            $streamEvents[] = $this->translateToStreamEvent($aggregateChangedEvent);
        }

        return $streamEvents;
    }

    /**
     * @param \Prooph\EventStore\Stream\StreamId $streamId
     * @param StreamEvent[] $streamEvents
     * @return AggregateChangedEvent[]
     */
    public function toAggregateChangedEvents(StreamId $streamId, array $streamEvents)
    {
        $aggregateChangedEvents = array();

        foreach ($streamEvents as $streamEvent)
        {
            $aggregateChangedEvents[] = $this->translateToAggregateChangedEvent($streamId, $streamEvent);
        }

        return $aggregateChangedEvents;
    }

    /**
     * @return EventManager
     */
    public function getLifeCycleEvents()
    {
        if (is_null($this->lifeCycleEvents)) {
            $this->setEventManager(new EventManager());
        }

        return $this->lifeCycleEvents;
    }

    /**
     * @param EventManager $eventManager
     */
    public function setEventManager(EventManager $eventManager)
    {
        $eventManager->attach('translateStreamId', function (Event $e) {
            return $e->getParam('streamId')->toString();
        }, -100);

        $eventManager->addIdentifiers(array(
            'AggregateChangedEventHydrator',
            get_class($this)
        ));

        $this->lifeCycleEvents = $eventManager;

    }


    /**
     * @param AggregateChangedEvent $aggregateChangedEvent
     * @return StreamEvent
     */
    protected function translateToStreamEvent(AggregateChangedEvent $aggregateChangedEvent)
    {
        return new StreamEvent(
            new EventId($aggregateChangedEvent->uuid()->toString()),
            new EventName(get_class($aggregateChangedEvent)),
            $aggregateChangedEvent->payload(),
            $aggregateChangedEvent->version(),
            $aggregateChangedEvent->occurredOn()->toNativeDateTime()
        );
    }

    /**
     * @param StreamId $streamId
     * @param StreamEvent $streamEvent
     * @return AggregateChangedEvent
     * @throws \RuntimeException if construction fails
     */
    protected function translateToAggregateChangedEvent(StreamId $streamId, StreamEvent $streamEvent)
    {
        if (! class_exists($streamEvent->eventName())) {
            throw new \RuntimeException(
                sprintf(
                    'Event %s can not be constructed. EventName is no valid class name',
                    $streamEvent->eventName()
                )
            );
        }

        $eventRef = new \ReflectionClass($streamEvent->eventName()->toString());

        $event = $eventRef->newInstanceWithoutConstructor();

        if (! $event instanceof AggregateChangedEvent) {
            throw new \RuntimeException(
                sprintf(
                    'Event %s can not be constructed. It is not a Prooph\EventSourcing\AggregateChangedEvent',
                    $streamEvent->eventName()
                )
            );
        }

        $uuidProp = $eventRef->getProperty('uuid');

        $uuidProp->setAccessible(true);

        $uuidProp->setValue($event, Uuid::fromString($streamEvent->eventId()->toString()));

        $aggregateIdProp = $eventRef->getProperty('aggregateId');

        $aggregateIdProp->setAccessible(true);

        $result = $this->getLifeCycleEvents()->triggerUntil('translateStreamId', $this, array('streamId' => $streamId), function ($res) {
            return ! is_null($res);
        });

        if ($result->stopped()) {
            $aggregateId = $result->last();
        } else {
            throw new \RuntimeException(
                sprintf(
                    "StreamId %s could not be translated to AggregateId",
                    $streamId->toString()
                )
            );
        }

        $aggregateIdProp->setValue($event, $aggregateId);

        $occurredOnProp = $eventRef->getProperty('occurredOn');

        $occurredOnProp->setAccessible(true);

        $occurredOnProp->setValue($event, DateTime::fromNativeDateTime($streamEvent->occurredOn()));

        $versionProp = $eventRef->getProperty('version');

        $versionProp->setAccessible(true);

        $versionProp->setValue($event, $streamEvent->version());

        $payloadProp = $eventRef->getProperty('payload');

        $payloadProp->setAccessible(true);

        $payloadProp->setValue($event, $streamEvent->payload());

        return $event;
    }
}
 