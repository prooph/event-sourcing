<?php
/*
 * This file is part of the prooph/event-sourcing.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 07.09.14 - 19:49
 */

namespace Prooph\EventSourcingTest\EventStoreIntegration;

use Prooph\EventSourcing\EventStoreIntegration\AggregateTranslator;
use Prooph\EventSourcingTest\Mock\User;
use Prooph\EventSourcingTest\TestCase;
use Prooph\EventStore\Adapter\InMemoryAdapter;
use Prooph\EventStore\Aggregate\AggregateRepository;
use Prooph\EventStore\Aggregate\AggregateType;
use Prooph\EventStore\Configuration\Configuration;
use Prooph\EventStore\EventStore;
use Prooph\EventStore\Stream\SingleStreamStrategy;
use Prooph\EventStore\Stream\Stream;
use Prooph\EventStore\Stream\StreamName;

/**
 * Class AggregateTranslatorTest
 *
 * @package Prooph\EventSourcingTest\EventStoreIntegration
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class AggregateTranslatorTest extends TestCase
{
    /**
     * @var EventStore
     */
    protected $eventStore;

    /**
     * @var AggregateRepository
     */
    protected $repository;

    protected function setUp()
    {
        $esConfiguration = new Configuration();

        $esConfiguration->setAdapter(new InMemoryAdapter());

        $this->eventStore = new EventStore($esConfiguration);

        $this->eventStore->beginTransaction();

        $this->eventStore->create(new Stream(new StreamName('event_stream'), array()));

        $this->eventStore->commit();

        $this->resetRepository();
    }

    /**
     * @test
     */
    public function it_translates_aggregate_back_and_forth()
    {
        $this->eventStore->beginTransaction();

        $user = User::nameNew('John Doe');

        $user->changeName('Max Mustermann');

        $this->repository->addAggregateRoot($user);

        $this->eventStore->commit();

        $this->resetRepository();

        $loadedUser = $this->repository->getAggregateRoot($user->id());

        $this->assertEquals('Max Mustermann', $loadedUser->name());
    }

    protected function resetRepository()
    {
        $this->repository = new AggregateRepository(
            $this->eventStore,
            new AggregateTranslator(),
            new SingleStreamStrategy($this->eventStore),
            AggregateType::fromAggregateRootClass('Prooph\EventSourcingTest\Mock\User')
        );
    }
}
 