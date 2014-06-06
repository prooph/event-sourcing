<?php
/*
 * This file is part of the prooph/event-sourcing package.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Prooph\EventSourcing\Repository;

use Prooph\EventSourcing\AggregateChangedEvent;
use Prooph\EventSourcing\EventStore;
use Prooph\EventSourcing\EventSourcedAggregateRoot;
use Prooph\EventSourcing\Exception\InvalidArgumentException;
use Prooph\EventSourcing\Mapping\AggregateRootDecorator;

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
     * Type of the EventSourcedAggregateRoot for that the repository is responsible
     * 
     * @var string
     */
    protected $aggregateType;

    /**
     * @var AggregateRootDecorator
     */
    protected $aggregateRootDecorator;


    /**
     * @param EventStore $eventStore
     * @param string $aggregateType
     */
    public function __construct(EventStore $eventStore, $aggregateType)
    {
        $this->eventStore = $eventStore;
        $this->aggregateType = $aggregateType;
    }

    /**
     * Add an EventSourcedAggregateRoot
     *
     * @param EventSourcedAggregateRoot $anEventSourcedAggregateRoot
     * @throws \Prooph\EventSourcing\Exception\InvalidArgumentException If AggregateRoot FQCN does not match
     * @return void
     */
    public function add(EventSourcedAggregateRoot $anEventSourcedAggregateRoot)
    {
        try {
            \Assert\that($anEventSourcedAggregateRoot)->isInstanceOf($this->aggregateType);
        } catch (\InvalidArgumentException $ex) {
            throw new InvalidArgumentException($ex->getMessage());
        }

        $this->eventStore->attach($anEventSourcedAggregateRoot);
    }

    /**
     * Get an EventSourcedAggregateRoot by it's id
     *
     * @param string $anAggregateId
     *
     * @return EventSourcedAggregateRoot|null
     */
    public function get($anAggregateId)
    {
        return $this->eventStore->find($this->aggregateType, $anAggregateId);
    }

    /**
     * Remove an EventSourcedAggregateRoot
     *
     * @param \Prooph\EventSourcing\EventSourcedAggregateRoot $anEventSourcedAggregateRoot
     * @return void
     */
    public function remove(EventSourcedAggregateRoot $anEventSourcedAggregateRoot)
    {
        $this->eventStore->detach($anEventSourcedAggregateRoot);
    }

    //@TODO: add method removeAll



    /**
     * @param object $anEventSourcedAggregateRoot
     * @return string representation of the EventSourcedAggregateRoot
     */
    public function extractAggregateIdAsString($anEventSourcedAggregateRoot)
    {
        return (string)$this->getAggregateRootDecorator()->getAggregateId($anEventSourcedAggregateRoot);
    }

    /**
     * @param string $anAggregateType
     * @param string $anAggregateId
     * @param AggregateChangedEvent[] $historyEvents
     * @return object reconstructed EventSourcedAggregateRoot
     */
    public function constructAggregateFromHistory($anAggregateType, $anAggregateId, array $historyEvents)
    {
        $ref = new \ReflectionClass($anAggregateType);

        $prototype = $ref->newInstanceWithoutConstructor();

        return $this->getAggregateRootDecorator()->fromHistory($prototype, $anAggregateId, $historyEvents);
    }

    /**
     * @param object $anEventSourcedAggregateRoot
     * @return AggregateChangedEvent[]
     */
    public function extractPendingEvents($anEventSourcedAggregateRoot)
    {
        return $this->getAggregateRootDecorator()->extractPendingEvents($anEventSourcedAggregateRoot);
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
