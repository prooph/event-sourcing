<?php
/**
 * This file is part of the prooph/event-sourcing.
 * (c) 2014-2016 prooph software GmbH <contact@prooph.de>
 * (c) 2015-2016 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Prooph\EventSourcing\Aggregate;

use ArrayIterator;
use Prooph\Common\Messaging\Message;
use Prooph\EventSourcing\Snapshot\SnapshotStore;
use Prooph\EventStore\ActionEventEmitterEventStore;
use Prooph\EventStore\EventStore;
use Prooph\EventStore\Metadata\MetadataMatcher;
use Prooph\EventStore\Metadata\Operator;
use Prooph\EventStore\Stream;
use Prooph\EventStore\StreamName;
use Prooph\EventStore\TransactionalActionEventEmitterEventStore;

class AggregateRepository
{
    /**
     * @var EventStore
     */
    protected $eventStore;

    /**
     * @var AggregateTranslator
     */
    protected $aggregateTranslator;

    /**
     * @var AggregateType
     */
    protected $aggregateType;

    /**
     * @var array
     */
    protected $identityMap = [];

    /**
     * @var SnapshotStore|null
     */
    protected $snapshotStore;

    /**
     * @var StreamName
     */
    protected $streamName;

    /**
     * @var bool
     */
    protected $oneStreamPerAggregate;

    public function __construct(
        EventStore $eventStore,
        AggregateType $aggregateType,
        AggregateTranslator $aggregateTranslator,
        SnapshotStore $snapshotStore = null,
        StreamName $streamName = null,
        bool $oneStreamPerAggregate = false
    ) {
        if (! $eventStore instanceof ActionEventEmitterEventStore) {
            throw new Exception\InvalidArgumentException(
                sprintf(
                    'EventStore must implement %s',
                    ActionEventEmitterEventStore::class
                )
            );
        }

        $this->eventStore = $eventStore;

        if ($eventStore instanceof TransactionalActionEventEmitterEventStore) {
            $this->eventStore->getActionEventEmitter()->attachListener(
                TransactionalActionEventEmitterEventStore::EVENT_COMMIT,
                function (): void {
                    foreach ($this->identityMap as $aggregateId => $aggregateRoot) {
                        $pendingStreamEvents = $this->aggregateTranslator->extractPendingStreamEvents($aggregateRoot);

                        if (count($pendingStreamEvents)) {
                            $enrichedEvents = [];

                            foreach ($pendingStreamEvents as $event) {
                                $enrichedEvents[] = $this->enrichEventMetadata($event, $aggregateId);
                            }

                            $streamName = $this->determineStreamName($aggregateId);

                            $this->eventStore->appendTo($streamName, new ArrayIterator($enrichedEvents));
                        }
                    }

                    //Clear identity map
                    $this->identityMap = [];
                },
                1000
            );
        }

        $this->aggregateType = $aggregateType;
        $this->aggregateTranslator = $aggregateTranslator;
        $this->snapshotStore = $snapshotStore;
        $this->streamName = $streamName;
        $this->oneStreamPerAggregate = $oneStreamPerAggregate;
    }

    /**
     * @param object $eventSourcedAggregateRoot
     */
    public function addAggregateRoot($eventSourcedAggregateRoot): void
    {
        $this->assertAggregateType($eventSourcedAggregateRoot);

        $domainEvents = $this->aggregateTranslator->extractPendingStreamEvents($eventSourcedAggregateRoot);

        $aggregateId = $this->aggregateTranslator->extractAggregateId($eventSourcedAggregateRoot);

        $streamName = $this->determineStreamName($aggregateId);

        $enrichedEvents = [];

        foreach ($domainEvents as $event) {
            $enrichedEvents[] = $this->enrichEventMetadata($event, $aggregateId);
        }

        if ($this->oneStreamPerAggregate) {
            $stream = new Stream($streamName, new ArrayIterator($enrichedEvents));

            $this->eventStore->create($stream);
        } else {
            $this->eventStore->appendTo($streamName, new ArrayIterator($enrichedEvents));
        }
    }

    /**
     * Returns null if no stream events can be found for aggregate root otherwise the reconstituted aggregate root
     *
     * @return null|object
     */
    public function getAggregateRoot(string $aggregateId)
    {
        if (isset($this->identityMap[$aggregateId])) {
            return $this->identityMap[$aggregateId];
        }

        if ($this->snapshotStore) {
            $eventSourcedAggregateRoot = $this->loadFromSnapshotStore($aggregateId);

            if ($eventSourcedAggregateRoot) {
                //Cache aggregate root in the identity map
                $this->identityMap[$aggregateId] = $eventSourcedAggregateRoot;

                return $eventSourcedAggregateRoot;
            }
        }

        $streamName = $this->determineStreamName($aggregateId);

        if ($this->oneStreamPerAggregate) {
            $stream = $this->eventStore->load($streamName, 1);
        } else {
            $metadataMatcher = new MetadataMatcher();
            $metadataMatcher = $metadataMatcher->withMetadataMatch(
                '_aggregate_type',
                Operator::EQUALS(),
                $this->aggregateType->toString()
            );
            $metadataMatcher = $metadataMatcher->withMetadataMatch(
                '_aggregate_id',
                Operator::EQUALS(),
                $aggregateId
            );

            $stream = $this->eventStore->load($streamName, 1, null, $metadataMatcher);
        }

        $streamEvents = $stream->streamEvents();

        if (! $streamEvents->valid()) {
            return;
        }

        $eventSourcedAggregateRoot = $this->aggregateTranslator->reconstituteAggregateFromHistory(
            $this->aggregateType,
            $streamEvents
        );

        //Cache aggregate root in the identity map but without pending events
        $this->identityMap[$aggregateId] = $eventSourcedAggregateRoot;

        return $eventSourcedAggregateRoot;
    }

    /**
     * @param object $aggregateRoot
     */
    public function extractAggregateVersion($aggregateRoot) : int
    {
        return $this->aggregateTranslator->extractAggregateVersion($aggregateRoot);
    }

    /**
     * Empties the identity map. Use this if you load thousands of aggregates to free memory e.g. modulo 500.
     */
    public function clearIdentityMap() : void
    {
        $this->identityMap = [];
    }

    /**
     * @return null|object
     */
    protected function loadFromSnapshotStore(string $aggregateId)
    {
        $snapshot = $this->snapshotStore->get($this->aggregateType, $aggregateId);

        if (! $snapshot) {
            return;
        }

        $aggregateRoot = $snapshot->aggregateRoot();

        $streamName = $this->determineStreamName($aggregateId);

        if ($this->oneStreamPerAggregate) {
            $stream = $this->eventStore->load(
                $streamName,
                $snapshot->lastVersion() + 1
            );
        } else {
            $metadataMatcher = new MetadataMatcher();
            $metadataMatcher = $metadataMatcher->withMetadataMatch(
                '_aggregate_type',
                Operator::EQUALS(),
                $this->aggregateType->toString()
            );
            $metadataMatcher = $metadataMatcher->withMetadataMatch(
                '_aggregate_id',
                Operator::EQUALS(),
                $aggregateId
            );
            $metadataMatcher = $metadataMatcher->withMetadataMatch(
                '_aggregate_version',
                Operator::GREATER_THAN(),
                $snapshot->lastVersion()
            );

            $stream = $this->eventStore->load(
                $streamName,
                1,
                null,
                $metadataMatcher
            );
        }

        $streamEvents = $stream->streamEvents();

        if (! $streamEvents->valid()) {
            return $aggregateRoot;
        }

        $this->aggregateTranslator->replayStreamEvents($aggregateRoot, $streamEvents);

        return $aggregateRoot;
    }

    /**
     * Default stream name generation.
     * Override this method in an extending repository to provide a custom name
     */
    protected function determineStreamName(string $aggregateId): StreamName
    {
        if ($this->oneStreamPerAggregate) {
            return new StreamName($this->aggregateType->toString() . '-' . $aggregateId);
        }

        if (null === $this->streamName) {
            return new StreamName('event_stream');
        }

        return $this->streamName;
    }

    /**
     * Add aggregate_id and aggregate_type as metadata to $domainEvent
     * Override this method in an extending repository to add more or different metadata.
     */
    protected function enrichEventMetadata(Message $domainEvent, string $aggregateId): Message
    {
        $domainEvent = $domainEvent->withAddedMetadata('_aggregate_id', $aggregateId);
        $domainEvent = $domainEvent->withAddedMetadata('_aggregate_type', $this->aggregateType->toString());

        return $domainEvent;
    }

    /**
     * @param object $eventSourcedAggregateRoot
     */
    protected function assertAggregateType($eventSourcedAggregateRoot)
    {
        $this->aggregateType->assert($eventSourcedAggregateRoot);
    }
}
