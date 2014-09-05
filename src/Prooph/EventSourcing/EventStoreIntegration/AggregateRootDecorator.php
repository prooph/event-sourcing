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
use Prooph\EventSourcing\LifeCycleEvent\GetIdentifierProperty;

/**
 * Class AggregateRootDecorator
 *
 * @package Prooph\EventSourcing\Mapping
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class AggregateRootDecorator extends AggregateRoot
{
    /**
     * @param AggregateRoot $anAggregate
     * @return \Prooph\EventSourcing\AggregateChangedEvent[]
     */
    public function extractPendingEvents(AggregateRoot $anAggregate)
    {
        return $anAggregate->getPendingEvents();
    }

    /**
     * @param AggregateRoot $anAggregate
     * @return mixed AggregateId
     */
    public function getAggregateId(AggregateRoot $anAggregate)
    {
        $result = $anAggregate->getLifeCycleEvents()->trigger(new GetIdentifierProperty($anAggregate));

        $property = $result->last();

        $aggregateRef = new \ReflectionClass($anAggregate);

        $propertyRef = $aggregateRef->getProperty($property);

        $propertyRef->setAccessible(true);

        return $propertyRef->getValue($anAggregate);
    }

    public function fromHistory($aggregatePrototype, $aggregateId, array $historyStream)
    {
        $aggregatePrototype->initializeFromHistory($aggregateId, $historyStream);

        return $aggregatePrototype;
    }
}
 