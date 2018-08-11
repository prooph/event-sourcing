<?php
/**
 * This file is part of the prooph/event-sourcing.
 * (c) 2014-2018 prooph software GmbH <contact@prooph.de>
 * (c) 2015-2018 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Prooph\EventSourcing\Aggregate;

use ArrayIterator;
use Prooph\Common\Messaging\Message;
use Prooph\EventSourcing\MessageTransformer;
use Prooph\EventStoreClient\EventStoreSyncConnection;
use Prooph\EventStoreClient\ExpectedVersion;
use Prooph\EventStoreClient\Internal\Consts;
use Prooph\EventStoreClient\SliceReadStatus;
use Prooph\EventStoreClient\UserCredentials;

class AggregateRepository
{
    /** @var EventStoreSyncConnection */
    protected $eventStoreConnection;
    /** @var AggregateTranslator */
    protected $aggregateTranslator;
    /** @var AggregateType */
    protected $aggregateType;
    /** @var MessageTransformer */
    protected $transformer;
    /** @var bool */
    protected $optimisticConcurrency;

    public function __construct(
        EventStoreSyncConnection $eventStoreConnection,
        AggregateType $aggregateType,
        AggregateTranslator $aggregateTranslator,
        MessageTransformer $transformer,
        bool $useOptimisticConcurrencyByDefault = true
    ) {
        $this->eventStoreConnection = $eventStoreConnection;
        $this->aggregateType = $aggregateType;
        $this->aggregateTranslator = $aggregateTranslator;
        $this->transformer = $transformer;
        $this->optimisticConcurrency = $useOptimisticConcurrencyByDefault;
    }

    public function saveAggregateRoot(
        object $eventSourcedAggregateRoot,
        int $expectedVersion = null,
        UserCredentials $credentials = null
    ): void {
        $this->aggregateType->assert($eventSourcedAggregateRoot);

        $domainEvents = $this->aggregateTranslator->extractPendingStreamEvents($eventSourcedAggregateRoot);

        if (empty($domainEvents)) {
            return;
        }

        $aggregateId = $this->aggregateTranslator->extractAggregateId($eventSourcedAggregateRoot);
        $stream = $this->aggregateType->streamCategory() . '-' . $aggregateId;

        $eventData = [];

        foreach ($domainEvents as $event) {
            $message = $this->enrichEventMetadata($eventSourcedAggregateRoot, $event);
            $eventData[] = $this->transformer->toEventData($message);
        }

        if (null === $expectedVersion) {
            if ($this->optimisticConcurrency) {
                $expectedVersion = $this->aggregateTranslator->extractExpectedVersion($eventSourcedAggregateRoot);
            } else {
                $expectedVersion = ExpectedVersion::Any;
            }
        }

        $writeResult = $this->eventStoreConnection->appendToStream(
            $stream,
            $expectedVersion,
            $eventData,
            $credentials
        );

        $this->aggregateTranslator->setExpectedVersion($eventSourcedAggregateRoot, $writeResult->nextExpectedVersion());
    }

    /**
     * Returns null if no stream events can be found for aggregate root otherwise the reconstituted aggregate root
     */
    public function getAggregateRoot(string $aggregateId, UserCredentials $credentials = null): ?object
    {
        $stream = $this->aggregateType->streamCategory() . '-' . $aggregateId;

        $start = 0;
        $count = Consts::MaxReadSize;

        do {
            $iterator = new ArrayIterator();

            $streamEventsSlice = $this->eventStoreConnection->readStreamEventsForward(
                $stream,
                $start,
                $count,
                true,
                $credentials
            );

            if (! $streamEventsSlice->status()->equals(SliceReadStatus::success())) {
                return null;
            }

            $start = $streamEventsSlice->nextEventNumber();

            foreach ($streamEventsSlice->events() as $event) {
                $iterator->append($this->transformer->toMessage($event));
            }

            if (isset($eventSourcedAggregateRoot)) {
                $this->aggregateTranslator->replayStreamEvents($eventSourcedAggregateRoot, $iterator);
            } else {
                $eventSourcedAggregateRoot = $this->aggregateTranslator->reconstituteAggregateFromHistory(
                    $this->aggregateType,
                    $iterator
                );
            }
        } while (! $streamEventsSlice->isEndOfStream());

        $this->aggregateTranslator->setExpectedVersion($eventSourcedAggregateRoot, $streamEventsSlice->lastEventNumber());

        return $eventSourcedAggregateRoot;
    }

    /**
     * Add aggregate_id and aggregate_type as metadata to $domainEvent
     * Override this method in an extending repository to add more or different metadata.
     */
    protected function enrichEventMetadata(object $eventSourcedAggregateRoot, Message $domainEvent): Message
    {
        $domainEvent = $domainEvent->withAddedMetadata('effective_time', $domainEvent->createdAt()->format('Y-m-d H:i:s.u'));
        $domainEvent = $domainEvent->withAddedMetadata('aggregate_type', $this->aggregateType->typeFromAggregate($eventSourcedAggregateRoot));

        return $domainEvent;
    }
}
