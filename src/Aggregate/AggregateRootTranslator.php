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

final class AggregateRootTranslator implements AggregateTranslator
{
    /**
     * @var AggregateRootDecorator
     */
    protected $aggregateRootDecorator;

    public function extractExpectedVersion(object $eventSourcedAggregateRoot): int
    {
        return $this->aggregateRootDecorator->extractExpectedVersion($eventSourcedAggregateRoot);
    }

    public function setExpectedVersion(object $eventSourcedAggregateRoot, int $expectedVersion): void
    {
        $this->aggregateRootDecorator->setExpectedVersion($eventSourcedAggregateRoot, $expectedVersion);
    }

    public function extractAggregateId(object $eventSourcedAggregateRoot): string
    {
        return $this->getAggregateRootDecorator()->extractAggregateId($eventSourcedAggregateRoot);
    }

    public function reconstituteAggregateFromHistory(AggregateType $aggregateType, Iterator $historyEvents): object
    {
        if (! $historyEvents->valid()) {
            throw new Exception\RuntimeException('History events are empty');
        }

        $firstEvent = $historyEvents->current();
        /* @var Message $firstEvent */
        $aggregateTypeString = $firstEvent->metadata()['aggregate_type'] ?? ''; // @todo

        $aggregateRootClass = $aggregateType->className($aggregateTypeString);

        return $this->getAggregateRootDecorator()
            ->fromHistory($aggregateRootClass, $historyEvents);
    }

    /**
     * @return Message[]
     */
    public function extractPendingStreamEvents(object $eventSourcedAggregateRoot): array
    {
        return $this->getAggregateRootDecorator()->extractRecordedEvents($eventSourcedAggregateRoot);
    }

    public function replayStreamEvents(object $eventSourcedAggregateRoot, Iterator $events): void
    {
        $this->getAggregateRootDecorator()->replayStreamEvents($eventSourcedAggregateRoot, $events);
    }

    public function getAggregateRootDecorator(): AggregateRootDecorator
    {
        if (null === $this->aggregateRootDecorator) {
            $this->aggregateRootDecorator = AggregateRootDecorator::newInstance();
        }

        return $this->aggregateRootDecorator;
    }

    public function setAggregateRootDecorator(AggregateRootDecorator $anAggregateRootDecorator): void
    {
        $this->aggregateRootDecorator = $anAggregateRootDecorator;
    }
}
