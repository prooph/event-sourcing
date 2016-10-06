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

namespace Prooph\EventSourcing\EventStoreIntegration;

use Iterator;
use Prooph\EventSourcing\AggregateRoot;

/**
 * Class AggregateRootDecorator
 *
 * @package Prooph\EventSourcing\Mapping
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class AggregateRootDecorator extends AggregateRoot
{
    public static function newInstance(): self
    {
        return new static();
    }

    public function extractAggregateVersion(AggregateRoot $anAggregateRoot): int
    {
        return $anAggregateRoot->version;
    }

    /**
     * @param AggregateRoot $anAggregateRoot
     *
     * @return \Prooph\EventSourcing\AggregateChanged[]
     */
    public function extractRecordedEvents(AggregateRoot $anAggregateRoot): array
    {
        return $anAggregateRoot->popRecordedEvents();
    }

    public function extractAggregateId(AggregateRoot $anAggregateRoot): string
    {
        return $anAggregateRoot->aggregateId();
    }

    /**
     * @throws \RuntimeException
     */
    public function fromHistory($arClass, \Iterator $aggregateChangedEvents): AggregateRoot
    {
        if (! class_exists($arClass)) {
            throw new \RuntimeException(
                sprintf('Aggregate root class %s cannot be found', $arClass)
            );
        }

        return $arClass::reconstituteFromHistory($aggregateChangedEvents);
    }

    public function replayStreamEvents(AggregateRoot $aggregateRoot, Iterator $events): void
    {
        $aggregateRoot->replay($events);
    }

    /**
     * @throws \BadMethodCallException
     */
    protected function aggregateId(): string
    {
        throw new \BadMethodCallException('The AggregateRootDecorator does not have an id');
    }
}
