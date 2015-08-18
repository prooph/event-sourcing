<?php
/*
 * This file is part of the prooph/event-store.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 19.04.14 - 20:24
 */

namespace Prooph\EventSourcing\EventStoreIntegration;

use Prooph\EventSourcing\AggregateRoot;

/**
 * Class AggregateRootDecorator
 *
 * @package Prooph\EventSourcing\Mapping
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class AggregateRootDecorator extends AggregateRoot
{
    public static function newInstance()
    {
        return new self();
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
     * @param array $aggregateChangedEvents
     * @return AggregateRoot
     * @throws \RuntimeException
     */
    public function fromHistory($arClass, array $aggregateChangedEvents)
    {
        if (! class_exists($arClass)) {
            throw new \RuntimeException(
                sprintf("Aggregate root class %s cannot be found", $arClass)
            );
        }

        return $arClass::reconstituteFromHistory($aggregateChangedEvents);
    }

    /**
     * @throws \BadMethodCallException
     * @return string representation of the unique identifier of the aggregate root
     */
    protected function aggregateId()
    {
        throw new \BadMethodCallException("The AggregateRootDecorator does not have an id");
    }
}
