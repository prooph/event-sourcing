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

namespace Prooph\EventSourcing\Snapshot;

use Prooph\EventSourcing\Aggregate\AggregateType;

final class InMemorySnapshotStore implements SnapshotStore
{
    /**
     * @var array
     */
    private $map = [];

    public function get(AggregateType $aggregateType, string $aggregateId): ?Snapshot
    {
        if (! isset($this->map[$aggregateType->toString()][$aggregateId])) {
            return null;
        }

        return $this->map[$aggregateType->toString()][$aggregateId];
    }

    public function save(Snapshot $snapshot): void
    {
        $this->map[$snapshot->aggregateType()->toString()][$snapshot->aggregateId()] = $snapshot;
    }
}
