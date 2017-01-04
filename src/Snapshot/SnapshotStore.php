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

namespace Prooph\EventSourcing\Snapshot;

use Prooph\EventSourcing\Aggregate\AggregateType;

interface SnapshotStore
{
    public function get(AggregateType $aggregateType, string $aggregateId): ?Snapshot;

    public function save(Snapshot $snapshot): void;
}
