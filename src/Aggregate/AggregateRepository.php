<?php
/**
 * This file is part of the prooph/event-sourcing.
 * (c) 2014-2017 prooph software GmbH <contact@prooph.de>
 * (c) 2015-2017 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Prooph\EventSourcing\Aggregate;

use ArrayIterator;
use Prooph\Common\Messaging\Message;
use Prooph\EventSourcing\Snapshot\SnapshotStore;
use Prooph\EventStore\EventStore;
use Prooph\EventStore\Exception\StreamNotFound;
use Prooph\EventStore\Metadata\MetadataMatcher;
use Prooph\EventStore\Metadata\Operator;
use Prooph\EventStore\Stream;
use Prooph\EventStore\StreamName;

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
        $this->eventStore = $eventStore;
        $this->aggregateType = $aggregateType;
        $this->aggregateTranslator = $aggregateTranslator;
        $this->snapshotStore = $snapshotStore;
        $this->streamName = $streamName;
        $this->oneStreamPerAggregate = $oneStreamPerAggregate;
    }

    /**
     * @param object $eventSourcedAggregateRoot
     */
    public function saveAggregateRoot($eventSourcedAggregateRoot): void
    {
        $this->assertAggregateType($eventSourcedAggregateRoot);

        $domainEvents = $this->aggregateTranslator->extractPendingStreamEvents($eventSourcedAggregateRoot);

        $aggregateId = $this->aggregateTranslator->extractAggregateId($eventSourcedAggregateRoot);

        $streamName = $this->determineStreamName($aggregateId);

        $enrichedEvents = [];

        $createStream = false;

        $firstEvent = $domainEvents[0];

        if ($this->isFirstEvent($firstEvent) && $this->oneStreamPerAggregate) {
            $createStream = true;
        }

        foreach ($domainEvents as $event) {
            $enrichedEvents[] = $this->enrichEventMetadata($event, $aggregateId);
        }

        if ($createStream) {
            $stream = new Stream($streamName, new ArrayIterator($enrichedEvents));

            $this->eventStore->create($stream);
        } else {
            $this->eventStore->appendTo($streamName, new ArrayIterator($enrichedEvents));
        }

        if (isset($this->identityMap[$aggregateId])) {
            unset($this->identityMap[$aggregateId]);
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
            }

            return $eventSourcedAggregateRoot;
        }

        $streamName = $this->determineStreamName($aggregateId);

        if ($this->oneStreamPerAggregate) {
            try {
                $stream = $this->eventStore->load($streamName, 1);
            } catch (StreamNotFound $e) {
                return null;
            }
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

            try {
                $stream = $this->eventStore->load($streamName, 1, null, $metadataMatcher);
            } catch (StreamNotFound $e) {
                return null;
            }
        }

        $streamEvents = $stream->streamEvents();

        if (! $streamEvents->valid()) {
            return null;
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
    public function extractAggregateVersion($aggregateRoot): int
    {
        return $this->aggregateTranslator->extractAggregateVersion($aggregateRoot);
    }

    /**
     * Empties the identity map. Use this if you load thousands of aggregates to free memory e.g. modulo 500.
     */
    public function clearIdentityMap(): void
    {
        $this->identityMap = [];
    }

    protected function isFirstEvent(Message $message): bool
    {
        return 1 === $message->metadata()['_aggregate_version'];
    }

    /**
     * @return null|object
     */
    protected function loadFromSnapshotStore(string $aggregateId)
    {
        $snapshot = $this->snapshotStore->get($this->aggregateType, $aggregateId);

        if ($snapshot) {
            $lastVersion = $snapshot->lastVersion();
            $aggregateRoot = $snapshot->aggregateRoot();
        } else {
            $lastVersion = 0;
            $aggregateRoot = null;
        }

        $streamName = $this->determineStreamName($aggregateId);

        if ($this->oneStreamPerAggregate) {
            try {
                $stream = $this->eventStore->load(
                    $streamName,
                    $lastVersion + 1
                );
            } catch (StreamNotFound $e) {
                return $aggregateRoot;
            }
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
                $lastVersion
            );

            try {
                $stream = $this->eventStore->load(
                    $streamName,
                    $lastVersion + 1,
                    null,
                    $metadataMatcher
                );
            } catch (StreamNotFound $e) {
                return $aggregateRoot;
            }
        }

        $streamEvents = $stream->streamEvents();

        if (! $streamEvents->valid()) {
            return $aggregateRoot;
        }

        if ($aggregateRoot) {
            $this->aggregateTranslator->replayStreamEvents($aggregateRoot, $streamEvents);
        } else {
            $aggregateRoot = $this->aggregateTranslator->reconstituteAggregateFromHistory(
                $this->aggregateType,
                $streamEvents
            );
        }

        return $aggregateRoot;
    }

    /**
     * Default stream name generation.
     * Override this method in an extending repository to provide a custom name
     */
    protected function determineStreamName(string $aggregateId): StreamName
    {
        if ($this->oneStreamPerAggregate) {
            if (null === $this->streamName) {
                $prefix = $this->aggregateType->toString();
            } else {
                $prefix = $this->streamName->toString();
            }

            return new StreamName($prefix . '-' . $aggregateId);
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
