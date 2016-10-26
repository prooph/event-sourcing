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

namespace Prooph\EventSourcing\Snapshot\Adapter;

use Prooph\EventSourcing\Aggregate\AggregateType;
use Prooph\EventSourcing\Snapshot\Snapshot;

/**
 * Interface Adapter
 * @package Prooph\EventSourcing\Snapshot\Adapter
 */
interface Adapter
{
    public function get(AggregateType $aggregateType, string $aggregateId): ?Snapshot;

    public function save(Snapshot $snapshot): void;
}
