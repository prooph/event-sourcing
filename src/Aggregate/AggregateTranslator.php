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

use Iterator;
use Prooph\Common\Messaging\Message;

interface AggregateTranslator
{
    public function extractExpectedVersion(object $eventSourcedAggregateRoot): int;

    public function setExpectedVersion(object $eventSourcedAggregateRoot, int $expectedVersion): void;

    public function extractAggregateId(object $eventSourcedAggregateRoot): string;

    public function reconstituteAggregateFromHistory(AggregateType $aggregateType, Iterator $historyEvents): object;

    /**
     * @param object $eventSourcedAggregateRoot
     *
     * @return Message[]
     */
    public function extractPendingStreamEvents(object $eventSourcedAggregateRoot): array;

    public function replayStreamEvents(object $eventSourcedAggregateRoot, Iterator $events): void;
}
