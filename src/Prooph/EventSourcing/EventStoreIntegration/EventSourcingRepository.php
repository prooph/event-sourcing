<?php
/*
 * This file is part of the prooph/event-sourcing package.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Prooph\EventSourcing\EventStoreIntegration;

use Prooph\EventSourcing\DomainEvent\AggregateChangedEvent;
use Prooph\EventSourcing\Exception\AggregateTypeMismatchException;
use Prooph\EventSourcing\Mapping\AggregateChangedEventHydrator;
use Prooph\EventSourcing\Mapping\EventHydratorInterface;
use Prooph\EventStore\EventStore;
use Prooph\EventSourcing\AggregateRoot;
use Prooph\EventSourcing\Mapping\AggregateRootDecorator;
use Prooph\EventStore\Repository\RepositoryInterface;
use Prooph\EventStore\Stream\AggregateType;
use Prooph\EventStore\Stream\Stream;
use Prooph\EventStore\Stream\StreamEvent;
use Prooph\EventStore\Stream\StreamId;

/**
 *  EventSourcingRepository
 * 
 * @author Alexander Miertsch <contact@prooph.de>
 * @package Prooph\EventSourcing\Repository
 */
class EventSourcingRepository implements RepositoryInterface
{
    /**
     * The EventStore instance
     * 
     * @var EventStore 
     */
    protected $eventStore;
    
    /**
     * Type of the AggregateRoot for that the repository is responsible
     * 
     * @var AggregateType
     */
    protected $aggregateType;

    /**
     * @var AggregateRootDecorator
     */
    protected $aggregateRootDecorator;

    /**
     * @var EventHydratorInterface
     */
    protected $eventHydrator;


    /**
     * @param EventStore $eventStore
     * @param AggregateType $aggregateType
     */
    public function __construct(EventStore $eventStore, AggregateType $aggregateType)
    {
        $this->eventStore = $eventStore;
        $this->aggregateType = $aggregateType;
    }

    /**
     * Add an AggregateRoot
     *
     * @param AggregateRoot $anEventSourcedAggregateRoot
     * @throws AggregateTypeMismatchException If AggregateRoot FQCN does not match
     * @return void
     */
    public function addToStore(AggregateRoot $anEventSourcedAggregateRoot)
    {
        try {
            \Assert\that($anEventSourcedAggregateRoot)->isInstanceOf($this->aggregateType->toString());
        } catch (\InvalidArgumentException $ex) {
            throw new AggregateTypeMismatchException($ex->getMessage());
        }

        $this->eventStore->attach($anEventSourcedAggregateRoot);
    }

    /**
     * Get an AggregateRoot by it's id
     *
     * @param string $anAggregateId
     *
     * @return AggregateRoot|null
     */
    public function getFromStore($anAggregateId)
    {
        return $this->eventStore->find($this->aggregateType, new StreamId((string)$anAggregateId));
    }

    /**
     * Remove an AggregateRoot
     *
     * @param \Prooph\EventSourcing\AggregateRoot $anEventSourcedAggregateRoot
     * @return void
     */
    public function removeFromStore(AggregateRoot $anEventSourcedAggregateRoot)
    {
        $this->eventStore->detach($anEventSourcedAggregateRoot);
    }

    //@TODO: add method removeAll

    /**
     *
     * @param \Prooph\EventStore\Stream\Stream $stream
     * @throws \RuntimeException
     * @return object reconstructed AggregateRoot
     */
    public function constructAggregateFromHistory(Stream $stream)
    {
        $ref = new \ReflectionClass($stream->aggregateType()->toString());

        $prototype = $ref->newInstanceWithoutConstructor();

        $aggregateChangedEvents = $this->getEventHydrator()->toAggregateChangedEvents($stream->streamId(), $stream->streamEvents());

        if (count($aggregateChangedEvents) === 0) {
            throw new \RuntimeException(
                sprintf(
                    "Can not construct Aggregate %s (id: %s) from history. No stream events given",
                    $stream->aggregateType()->toString(),
                    $stream->streamId()->toString()
                )
            );
        }

        return $this->getAggregateRootDecorator()
            ->fromHistory($prototype, $aggregateChangedEvents[0]->aggregateId(), $aggregateChangedEvents);
    }

    /**
     * @param object $anEventSourcedAggregateRoot
     * @return StreamId representation of the AggregateRoot
     */
    public function extractStreamId($anEventSourcedAggregateRoot)
    {
        $aggregateId = (string)$this->getAggregateRootDecorator()->getAggregateId($anEventSourcedAggregateRoot);

        return new StreamId($aggregateId);
    }

    /**
     * @param object $anEventSourcedAggregateRoot
     * @return StreamEvent[]
     */
    public function extractPendingStreamEvents($anEventSourcedAggregateRoot)
    {
        $aggregateChangedEvents = $this->getAggregateRootDecorator()->extractPendingEvents($anEventSourcedAggregateRoot);

        return $this->getEventHydrator()->toStreamEvents($aggregateChangedEvents);
    }

    /**
     * @return EventHydratorInterface
     */
    protected function getEventHydrator()
    {
        if (is_null($this->eventHydrator)) {
            $this->eventHydrator = new AggregateChangedEventHydrator();
        }

        return $this->eventHydrator;
    }

    /**
     * @return AggregateRootDecorator
     */
    protected function getAggregateRootDecorator()
    {
        if (is_null($this->aggregateRootDecorator)) {
            $this->aggregateRootDecorator = new AggregateRootDecorator();
        }

        return $this->aggregateRootDecorator;
    }

    /**
     * @param AggregateRootDecorator $anAggregateRootDecorator
     */
    protected function setAggregateRootDecorator(AggregateRootDecorator $anAggregateRootDecorator)
    {
        $this->aggregateRootDecorator = $anAggregateRootDecorator;
    }
}
