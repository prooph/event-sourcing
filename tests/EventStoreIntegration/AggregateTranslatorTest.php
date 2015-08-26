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

use Prooph\Common\Event\ProophActionEventEmitter;
use Prooph\EventSourcing\EventStoreIntegration\AggregateRootDecorator;
use Prooph\EventSourcing\EventStoreIntegration\AggregateTranslator;
use Prooph\EventSourcingTest\Mock\User;
use Prooph\EventSourcingTest\TestCase;
use Prooph\EventStore\Adapter\InMemoryAdapter;
use Prooph\EventStore\Aggregate\AggregateRepository;
use Prooph\EventStore\Aggregate\AggregateType;
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
        $this->eventStore = new EventStore(new InMemoryAdapter(), new ProophActionEventEmitter());

        $this->eventStore->beginTransaction();

        $this->eventStore->create(new Stream(new StreamName('event_stream'), []));

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

    /**
     * @test
     */
    public function it_can_use_custom_aggregate_root_decorator()
    {
        $mock = $this->getMock(AggregateRootDecorator::class, [], [], '', false);

        $translator = new AggregateTranslator();
        $translator->setAggregateRootDecorator($mock);

        $this->assertSame($mock, $translator->getAggregateRootDecorator());
    }

    /**
     * @test
     * @expectedException RuntimeException
     * @expectedExceptionMessage Can not reconstitute Aggregate Prooph\EventSourcingTest\Mock\User from history. No stream events given
     */
    public function it_cannot_reconstitute_from_history_without_stream_events()
    {
        $aggregateType = AggregateType::fromAggregateRootClass('Prooph\EventSourcingTest\Mock\User');

        $translator = new AggregateTranslator();
        $translator->reconstituteAggregateFromHistory($aggregateType, []);
    }

    protected function resetRepository()
    {
        $this->repository = new AggregateRepository(
            $this->eventStore,
            AggregateType::fromAggregateRootClass('Prooph\EventSourcingTest\Mock\User'),
            new AggregateTranslator(),
            new SingleStreamStrategy($this->eventStore)
        );
    }
}
