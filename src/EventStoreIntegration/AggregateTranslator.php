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

namespace Prooph\EventSourcing\EventStoreIntegration;

use Iterator;
use Prooph\Common\Messaging\Message;
use Prooph\EventSourcing\Aggregate\AggregateTranslator as EventStoreAggregateTranslator;
use Prooph\EventSourcing\Aggregate\AggregateType;

final class AggregateTranslator implements EventStoreAggregateTranslator
{
    /**
     * @var AggregateRootDecorator
     */
    protected $aggregateRootDecorator;

    /**
     * @param object $eventSourcedAggregateRoot
     *
     * @return int
     */
    public function extractAggregateVersion($eventSourcedAggregateRoot): int
    {
        return $this->getAggregateRootDecorator()->extractAggregateVersion($eventSourcedAggregateRoot);
    }

    /**
     * @param object $anEventSourcedAggregateRoot
     *
     * @return string
     */
    public function extractAggregateId($anEventSourcedAggregateRoot): string
    {
        return $this->getAggregateRootDecorator()->extractAggregateId($anEventSourcedAggregateRoot);
    }

    /**
     * @param AggregateType $aggregateType
     * @param Iterator $historyEvents
     *
     * @return object reconstructed AggregateRoot
     */
    public function reconstituteAggregateFromHistory(AggregateType $aggregateType, Iterator $historyEvents)
    {
        if (! $aggregateRootClass = $aggregateType->mappedClass()) {
            $aggregateRootClass = $aggregateType->toString();
        }

        return $this->getAggregateRootDecorator()
            ->fromHistory($aggregateRootClass, $historyEvents);
    }

    /**
     * @param object $anEventSourcedAggregateRoot
     *
     * @return Message[]
     */
    public function extractPendingStreamEvents($anEventSourcedAggregateRoot): array
    {
        return $this->getAggregateRootDecorator()->extractRecordedEvents($anEventSourcedAggregateRoot);
    }

    /**
     * @param object $anEventSourcedAggregateRoot
     * @param Iterator $events
     *
     * @return void
     */
    public function replayStreamEvents($anEventSourcedAggregateRoot, Iterator $events): void
    {
        $this->getAggregateRootDecorator()->replayStreamEvents($anEventSourcedAggregateRoot, $events);
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
