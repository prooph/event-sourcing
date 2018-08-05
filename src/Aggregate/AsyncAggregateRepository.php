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

use Amp\Promise;
use Amp\Success;
use ArrayIterator;
use Generator;
use Prooph\Common\Messaging\Message;
use Prooph\EventSourcing\MessageTransformer;
use Prooph\EventStoreClient\EventStoreAsyncConnection;
use Prooph\EventStoreClient\ExpectedVersion;
use Prooph\EventStoreClient\Internal\Consts;
use Prooph\EventStoreClient\SliceReadStatus;
use Prooph\EventStoreClient\StreamEventsSlice;
use Prooph\EventStoreClient\WriteResult;
use function Amp\call;

class AsyncAggregateRepository
{
    /** @var EventStoreAsyncConnection */
    protected $eventStoreConnection;
    /** @var AggregateTranslator */
    protected $aggregateTranslator;
    /** @var AggregateType */
    protected $aggregateType;
    /** @var MessageTransformer */
    protected $transformer;
    /** @var string */
    protected $category;
    /** @var bool */
    protected $optimisticConcurrency;

    public function __construct(
        EventStoreAsyncConnection $eventStoreConnection,
        AggregateType $aggregateType,
        AggregateTranslator $aggregateTranslator,
        MessageTransformer $transformer,
        string $category,
        bool $useOptimisticConcurrencyByDefault = true
    ) {
        $this->eventStoreConnection = $eventStoreConnection;
        $this->aggregateType = $aggregateType;
        $this->aggregateTranslator = $aggregateTranslator;
        $this->transformer = $transformer;
        $this->category = $category;
        $this->optimisticConcurrency = $useOptimisticConcurrencyByDefault;
    }

    public function saveAggregateRoot(object $eventSourcedAggregateRoot, int $expectedVersion = null): Promise
    {
        return call(function () use ($eventSourcedAggregateRoot, $expectedVersion): Generator {
            $this->aggregateType->assert($eventSourcedAggregateRoot);

            $domainEvents = $this->aggregateTranslator->extractPendingStreamEvents($eventSourcedAggregateRoot);

            if (empty($domainEvents)) {
                return yield new Success();
            }

            $aggregateId = $this->aggregateTranslator->extractAggregateId($eventSourcedAggregateRoot);
            $stream = $this->category . '-' . $aggregateId;

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

            /** @var WriteResult $writeResult */
            $writeResult = yield $this->eventStoreConnection->appendToStreamAsync(
                $stream,
                $expectedVersion,
                $eventData
            );

            $this->aggregateTranslator->setExpectedVersion($eventSourcedAggregateRoot, $writeResult->nextExpectedVersion());

            return yield new Success();
        });
    }

    /**
     * Returns null if no stream events can be found for aggregate root otherwise the reconstituted aggregate root
     */
    public function getAggregateRoot(string $aggregateId): Promise
    {
        return call(function () use ($aggregateId): Generator {
            $stream = $this->category . '-' . $aggregateId;

            $start = 0;
            $count = Consts::MaxReadSize;

            do {
                $iterator = new ArrayIterator();

                /** @var StreamEventsSlice $streamEventsSlice */
                $streamEventsSlice = yield $this->eventStoreConnection->readStreamEventsForwardAsync(
                    $stream,
                    $start,
                    $count,
                    true
                );

                if (! $streamEventsSlice->status()->equals(SliceReadStatus::success())) {
                    return yield new Success(null);
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

            return yield new Success($eventSourcedAggregateRoot);
        });
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
