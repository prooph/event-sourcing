<?php
/*
 * This file is part of the prooph/event-sourcing.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 05.09.14 - 23:36
 */

namespace Prooph\EventSourcing\EventStoreIntegration;

use Prooph\EventStore\Aggregate\AggregateTranslatorInterface;
use Prooph\EventStore\Aggregate\AggregateType;
use Prooph\EventStore\Stream\StreamEvent;

/**
 * Class AggregateTranslator
 *
 * @package Prooph\EventSourcing\EventStoreIntegration
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class AggregateTranslator implements AggregateTranslatorInterface
{
    /**
     * @var AggregateRootDecorator
     */
    protected $aggregateRootDecorator;

    /**
     * @var EventHydratorInterface
     */
    protected $eventHydrator;

    /**
     * @param object $anEventSourcedAggregateRoot
     * @return string
     */
    public function extractAggregateId($anEventSourcedAggregateRoot)
    {
        return (string)$this->getAggregateRootDecorator()->extractAggregateId($anEventSourcedAggregateRoot);
    }

    /**
     * @param AggregateType $aggregateType
     * @param StreamEvent[] $historyEvents
     * @throws \RuntimeException
     * @return object reconstructed AggregateRoot
     */
    public function reconstituteAggregateFromHistory(AggregateType $aggregateType, array $historyEvents)
    {
        $aggregateChangedEvents = $this->getEventHydrator()->toAggregateChangedEvents($historyEvents);

        if (count($aggregateChangedEvents) === 0) {
            throw new \RuntimeException(
                sprintf(
                    "Can not reconstitute Aggregate %s from history. No stream events given",
                    $aggregateType->toString()
                )
            );
        }

        return $this->getAggregateRootDecorator()
            ->fromHistory($aggregateType->toString(), $aggregateChangedEvents);
    }

    /**
     * @param object $anEventSourcedAggregateRoot
     * @return StreamEvent[]
     */
    public function extractPendingStreamEvents($anEventSourcedAggregateRoot)
    {
        $aggregateChangedEvents = $this->getAggregateRootDecorator()->extractRecordedEvents($anEventSourcedAggregateRoot);

        return $this->getEventHydrator()->toStreamEvents($aggregateChangedEvents);
    }

    /**
     * @return AggregateRootDecorator
     */
    public function getAggregateRootDecorator()
    {
        if (is_null($this->aggregateRootDecorator)) {
            $this->aggregateRootDecorator = AggregateRootDecorator::newInstance();
        }

        return $this->aggregateRootDecorator;
    }

    /**
     * @param AggregateRootDecorator $anAggregateRootDecorator
     */
    public function setAggregateRootDecorator(AggregateRootDecorator $anAggregateRootDecorator)
    {
        $this->aggregateRootDecorator = $anAggregateRootDecorator;
    }

    /**
     * @return EventHydratorInterface
     */
    public function getEventHydrator()
    {
        if (is_null($this->eventHydrator)) {
            $this->eventHydrator = new AggregateChangedEventHydrator();
        }

        return $this->eventHydrator;
    }

    public function setEventHydrator(EventHydratorInterface $eventHydrator)
    {
        $this->eventHydrator = $eventHydrator;
    }
}
 