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
use RuntimeException;

final class ClosureAggregateTranslator implements EventStoreAggregateTranslator
{
    protected $aggregateIdExtractor;
    protected $aggregateReconstructor;
    protected $pendingEventsExtractor;
    protected $replayStreamEvents;
    protected $versionExtractor;

    /**
     * @param object $eventSourcedAggregateRoot
     *
     * @return int
     */
    public function extractAggregateVersion($eventSourcedAggregateRoot): int
    {
        if (null === $this->versionExtractor) {
            $this->versionExtractor = function (): int {
                return $this->version;
            };
        }

        return $this->versionExtractor->call($eventSourcedAggregateRoot);
    }

    /**
     * @param object $anEventSourcedAggregateRoot
     *
     * @return string
     */
    public function extractAggregateId($anEventSourcedAggregateRoot): string
    {
        if (null === $this->aggregateIdExtractor) {
            $this->aggregateIdExtractor = function (): string {
                return $this->aggregateId();
            };
        }

        return $this->aggregateIdExtractor->call($anEventSourcedAggregateRoot);
    }

    /**
     * @param AggregateType $aggregateType
     * @param Iterator $historyEvents
     *
     * @return object reconstructed AggregateRoot
     */
    public function reconstituteAggregateFromHistory(AggregateType $aggregateType, Iterator $historyEvents)
    {
        if (null === $this->aggregateReconstructor) {
            $this->aggregateReconstructor = function ($historyEvents) {
                return static::reconstituteFromHistory($historyEvents);
            };
        }

        $arClass = $aggregateType->toString();

        if (! \class_exists($arClass)) {
            throw new RuntimeException(
                \sprintf('Aggregate root class %s cannot be found', $arClass)
            );
        }

        return ($this->aggregateReconstructor->bindTo(null, $arClass))($historyEvents);
    }

    /**
     * @param object $anEventSourcedAggregateRoot
     *
     * @return Message[]
     */
    public function extractPendingStreamEvents($anEventSourcedAggregateRoot): array
    {
        if (null === $this->pendingEventsExtractor) {
            $this->pendingEventsExtractor = function (): array {
                return $this->popRecordedEvents();
            };
        }

        return $this->pendingEventsExtractor->call($anEventSourcedAggregateRoot);
    }

    /**
     * @param object $anEventSourcedAggregateRoot
     * @param Iterator $events
     *
     * @return void
     */
    public function replayStreamEvents($anEventSourcedAggregateRoot, Iterator $events): void
    {
        if (null === $this->replayStreamEvents) {
            $this->replayStreamEvents = function ($events): void {
                $this->replay($events);
            };
        }
        $this->replayStreamEvents->call($anEventSourcedAggregateRoot, $events);
    }
}
