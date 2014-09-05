<?php
/*
 * This file is part of the prooph/event-store.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 17.04.14 - 22:01
 */

namespace Prooph\EventSourcing\LifeCycleEvent;

use Prooph\EventSourcing\DomainEvent\AggregateChangedEvent;
use Prooph\EventSourcing\AggregateRoot;
use Zend\EventManager\Event;

/**
 * Class UpdateAggregateRootEvent
 *
 * @package Prooph\EventSourcing\LifeCycleEvent
 * @author Alexander Miertsch <contact@prooph.de>
 */
class DetermineEventHandler extends Event
{
    const NAME = "DetermineEventHandler";

    /**
     * @param AggregateRoot $anAggregateRoot
     * @param AggregateChangedEvent $anAggregateChangedEvent
     */
    public function __construct(AggregateRoot $anAggregateRoot, AggregateChangedEvent $anAggregateChangedEvent)
    {
        $this->setName(self::NAME);
        $this->setTarget($anAggregateRoot);
        $this->setParam('aggregate_changed_event', $anAggregateChangedEvent);
    }

    /**
     * @return AggregateChangedEvent
     */
    public function getAggregateChangedEvent()
    {
        return $this->getParam('aggregate_changed_event');
    }
}
 