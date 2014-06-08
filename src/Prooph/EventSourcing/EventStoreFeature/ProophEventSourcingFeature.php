<?php
/*
 * This file is part of the prooph/event-sourcing.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 08.06.14 - 12:44
 */

namespace Prooph\EventSourcing\EventStoreFeature;

use Prooph\EventStore\EventStore;
use Prooph\EventStore\Feature\FeatureInterface;
use Prooph\EventSourcing\Repository\EventSourcingRepository;
use Zend\EventManager\Event;

/**
 * Class ProophEventSourcingFeature
 *
 * @package Prooph\EventStore\Adapter\EventStoreFeature
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class ProophEventSourcingFeature implements FeatureInterface
{
    /**
     * @param EventStore $eventStore
     * @return void
     */
    public function setUp(EventStore $eventStore)
    {
        $eventStore->getPersistenceEvents()->attach('getRepository', array($this, 'onGetRepository'), -90);
    }

    /**
     * @param Event $e
     * @return EventSourcingRepository
     */
    public function onGetRepository(Event $e)
    {
        return new EventSourcingRepository($e->getParam('eventStore'), $e->getParam('aggregateType'));
    }
}
 