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

namespace ProophTest\EventSourcing\Mock;

use Prooph\EventSourcing\Aggregate\AggregateRepository;
use Prooph\EventSourcing\Aggregate\AggregateTranslator;
use Prooph\EventSourcing\Aggregate\AggregateType;
use Prooph\EventStore\EventStore;
use Prooph\EventStore\StreamName;
use Prooph\SnapshotStore\SnapshotStore;

final class RepositoryMock extends AggregateRepository
{
    public function accessEventStore(): EventStore
    {
        return $this->eventStore;
    }

    public function accessAggregateType(): AggregateType
    {
        return $this->aggregateType;
    }

    public function accessAggregateTranslator(): AggregateTranslator
    {
        return $this->aggregateTranslator;
    }

    public function accessDeterminedStreamName(string $aggregateId = null): StreamName
    {
        return $this->determineStreamName($aggregateId);
    }

    public function accessOneStreamPerAggregateFlag(): bool
    {
        return $this->oneStreamPerAggregate;
    }

    public function accessSnapshotStore(): SnapshotStore
    {
        return $this->snapshotStore;
    }
}
