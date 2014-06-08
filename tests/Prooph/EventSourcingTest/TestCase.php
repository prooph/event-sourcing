<?php
/*
 * This file is part of the prooph/event-sourcing.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 06.06.14 - 23:05
 */

namespace Prooph\EventSourcingTest;

use Prooph\EventSourcing\EventStoreFeature\ProophEventSourcingFeature;
use Prooph\EventStore\Adapter\Zf2\Zf2EventStoreAdapter;
use Prooph\EventStore\Configuration\Configuration;
use Prooph\EventStore\EventStore;
use Zend\Math\BigInteger\Adapter\AdapterInterface;

/**
 * Class TestCase
 *
 * @package Prooph\EventSourcingTest
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class TestCase extends \PHPUnit_Framework_TestCase
{
    /**
     *
     * @var AdapterInterface
     */
    protected $eventStoreAdapter;

    /**
     * @var EventStore
     */
    protected $eventStore;

    protected function tearDown()
    {
        $this->getTestEventStore()->clear();
    }

    protected function initEventStoreAdapter()
    {
        $options = array(
            'connection' => array(
                'driver' => 'Pdo_Sqlite',
                'database' => ':memory:'
            )
        );

        $this->eventStoreAdapter = new Zf2EventStoreAdapter($options);
    }

    /**
     * @return AdapterInterface
     */
    protected function getEventStoreAdapter()
    {
        if (is_null($this->eventStoreAdapter)) {
            $this->initEventStoreAdapter();
        }

        return $this->eventStoreAdapter;
    }

    /**
     * @return EventStore
     */
    protected function getTestEventStore()
    {
        if(is_null($this->eventStore)) {
            $config = new Configuration(array(
                'features' => array('ProophEventSourcingFeature')
            ));
            $config->setAdapter($this->getEventStoreAdapter());
            $config->getFeatureManager()->setService('ProophEventSourcingFeature', new ProophEventSourcingFeature());
            $this->eventStore = new EventStore($config);
        }

        return $this->eventStore;
    }
}
 