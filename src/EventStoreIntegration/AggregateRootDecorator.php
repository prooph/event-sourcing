<?php
/**
 * This file is part of the prooph/event-sourcing.
 *  (c) 2014-2016 prooph software GmbH <contact@prooph.de>
 *  (c) 2015-2016 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
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
    /**
     * @return static
     */
    public static function newInstance()
    {
        return new static();
    }

    /**
     * @param AggregateRoot $anAggregateRoot
     * @return int
     */
    public function extractAggregateVersion(AggregateRoot $anAggregateRoot)
    {
        return $anAggregateRoot->version;
    }

    /**
     * @param AggregateRoot $anAggregateRoot
     * @return \Prooph\EventSourcing\AggregateChanged[]
     */
    public function extractRecordedEvents(AggregateRoot $anAggregateRoot)
    {
        return $anAggregateRoot->popRecordedEvents();
    }

    /**
     * @param AggregateRoot $anAggregateRoot
     * @return string
     */
    public function extractAggregateId(AggregateRoot $anAggregateRoot)
    {
        return $anAggregateRoot->aggregateId();
    }

    /**
     * @param string $arClass
     * @param \Iterator $aggregateChangedEvents
     * @return AggregateRoot
     * @throws \RuntimeException
     */
    public function fromHistory($arClass, \Iterator $aggregateChangedEvents)
    {
        if (! class_exists($arClass)) {
            throw new \RuntimeException(
                sprintf('Aggregate root class %s cannot be found', $arClass)
            );
        }

        return $arClass::reconstituteFromHistory($aggregateChangedEvents);
    }

    /**
     * @param AggregateRoot $aggregateRoot
     * @param Iterator $events
     */
    public function replayStreamEvents(AggregateRoot $aggregateRoot, Iterator $events)
    {
        $aggregateRoot->replay($events);
    }

    /**
     * @throws \BadMethodCallException
     * @return string representation of the unique identifier of the aggregate root
     */
    protected function aggregateId()
    {
        throw new \BadMethodCallException('The AggregateRootDecorator does not have an id');
    }
}
