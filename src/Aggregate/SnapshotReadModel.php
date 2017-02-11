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
use Prooph\EventSourcing\AggregateChanged;
use Prooph\EventStore\Projection\ReadModel;
use Prooph\SnapshotStore\Snapshot;
use Prooph\SnapshotStore\SnapshotStore;

final class SnapshotReadModel implements ReadModel
{
    /**
     * @var AggregateRepository
     */
    protected $aggregateRepository;

    /**
     * @var AggregateTranslator
     */
    protected $aggregateTranslator;

    /**
     * @var array
     */
    protected $aggregateCache = [];

    /**
     * @var SnapshotStore
     */
    protected $snapshotStore;

    public function __construct(
        AggregateRepository $aggregateRepository,
        AggregateTranslator $aggregateTranslator,
        SnapshotStore $snapshotStore
    ) {
        $this->aggregateRepository = $aggregateRepository;
        $this->aggregateTranslator = $aggregateTranslator;
        $this->snapshotStore = $snapshotStore;
    }

    public function stack(string $operation, ...$events): void
    {
        $event = $events[0];

        if (! $event instanceof AggregateChanged) {
            throw new \RuntimeException(get_class($this) . ' can only handle events of type ' . AggregateChanged::class);
        }

        $aggregateId = $event->aggregateId();

        if (! isset($this->aggregateCache[$aggregateId])) {
            $aggregateRoot = $this->aggregateRepository->getAggregateRoot($aggregateId);

            if (! $aggregateRoot) {
                // this happens when you have multiple aggregate types in a single stream
                return;
            }

            $this->aggregateCache[$aggregateId] = $aggregateRoot;
        }

        $this->aggregateTranslator->replayStreamEvents(
            $this->aggregateCache[$aggregateId],
            new ArrayIterator([$event])
        );
    }

    public function persist(): void
    {
        $snapshots = [];

        foreach ($this->aggregateCache as $aggregateRoot) {
            $snapshots[] = new Snapshot(
                (string) AggregateType::fromAggregateRoot($aggregateRoot),
                $this->aggregateTranslator->extractAggregateId($aggregateRoot),
                $aggregateRoot,
                $this->aggregateTranslator->extractAggregateVersion($aggregateRoot),
                new \DateTimeImmutable('now', new \DateTimeZone('UTC'))
            );
        }

        $this->snapshotStore->save(...$snapshots);

        $this->aggregateCache = [];
    }

    public function init(): void
    {
        throw new \BadMethodCallException('Initializing a snapshot read model is not supported');
    }

    public function isInitialized(): bool
    {
        return true;
    }

    public function reset(): void
    {
        throw new \BadMethodCallException('Resetting a snapshot read model is not supported');
    }

    public function delete(): void
    {
        throw new \BadMethodCallException('Deleting a snapshot read model is not supported');
    }
}
