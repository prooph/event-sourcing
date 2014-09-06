<?php
/*
 * This file is part of the prooph/event-sourcing.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 08.06.14 - 14:21
 */

namespace Prooph\EventSourcing\EventStoreIntegration;
use Prooph\EventSourcing\AggregateChanged;
use Prooph\EventStore\Stream\StreamEvent;
use Prooph\EventStore\Stream\StreamId;

/**
 * Interface EventHydratorInterface
 *
 * @package Prooph\EventSourcing\Mapping
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
interface EventHydratorInterface 
{
    /**
     * @param AggregateChanged[] $aggregateChangedEvents
     * @return StreamEvent[]
     */
    public function toStreamEvents(array $aggregateChangedEvents);

    /**
     * @param StreamEvent[] $streamEvents
     * @return AggregateChanged[]
     */
    public function toAggregateChangedEvents(array $streamEvents);
}
 