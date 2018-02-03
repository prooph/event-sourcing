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
    /**
     * @param object $eventSourcedAggregateRoot
     */
    public function extractAggregateVersion($eventSourcedAggregateRoot): int;

    /**
     * @param object $eventSourcedAggregateRoot
     */
    public function extractAggregateId($eventSourcedAggregateRoot): string;

    /**
     * @return object reconstructed EventSourcedAggregateRoot
     */
    public function reconstituteAggregateFromHistory(AggregateType $aggregateType, Iterator $historyEvents);

    /**
     * @param object $eventSourcedAggregateRoot
     *
     * @return Message[]
     */
    public function extractPendingStreamEvents($eventSourcedAggregateRoot): array;

    /**
     * @param object $eventSourcedAggregateRoot
     * @param Iterator $events
     */
    public function replayStreamEvents($eventSourcedAggregateRoot, Iterator $events): void;
}
