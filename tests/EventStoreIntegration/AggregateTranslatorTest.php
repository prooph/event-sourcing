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

namespace ProophTest\EventSourcing\EventStoreIntegration;

use PHPUnit\Framework\TestCase;
use Prooph\Common\Event\ProophActionEventEmitter;
use Prooph\EventSourcing\Aggregate\AggregateRepository;
use Prooph\EventSourcing\Aggregate\AggregateType;
use Prooph\EventSourcing\EventStoreIntegration\AggregateRootDecorator;
use Prooph\EventSourcing\EventStoreIntegration\AggregateTranslator;
use Prooph\EventStore\EventStore;
use Prooph\EventStore\InMemoryEventStore;
use Prooph\EventStore\Stream;
use Prooph\EventStore\StreamName;
use ProophTest\EventSourcing\Mock\User;
use ProophTest\EventSourcing\Mock\UserNameChanged;

class AggregateTranslatorTest extends TestCase
{
    /**
     * @var InMemoryEventStore
     */
    protected $eventStore;

    /**
     * @var AggregateRepository
     */
    protected $repository;

    protected function setUp()
    {
        $this->eventStore = new InMemoryEventStore(new ProophActionEventEmitter());

        $this->eventStore->beginTransaction();

        $this->eventStore->create(new Stream(new StreamName('event_stream'), new \ArrayIterator([])));

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

        $this->repository->saveAggregateRoot($user);

        $this->eventStore->commit();

        $this->eventStore->beginTransaction();

        //Simulate a normal program flow by fetching the AR before modifying it
        $user = $this->repository->getAggregateRoot($user->id());

        $user->changeName('Max Mustermann');

        $this->eventStore->commit();

        $this->resetRepository();

        $loadedUser = $this->repository->getAggregateRoot($user->id());

        $this->assertEquals('Max Mustermann', $loadedUser->name());

        return $loadedUser;
    }

    /**
     * @test
     * @depends it_translates_aggregate_back_and_forth
     * @param User $loadedUser
     */
    public function it_extracts_version(User $loadedUser)
    {
        $translator = new AggregateTranslator();
        $this->assertEquals(2, $translator->extractAggregateVersion($loadedUser));
    }

    /**
     * @test
     * @depends it_translates_aggregate_back_and_forth
     * @param User $loadedUser
     */
    public function it_applies_stream_events(User $loadedUser)
    {
        $newName = 'Jane Doe';

        $translator = new AggregateTranslator();
        $translator->replayStreamEvents($loadedUser, new \ArrayIterator([UserNameChanged::occur($loadedUser->id(), [
            'username' => $newName,
        ])]));

        $this->assertEquals($newName, $loadedUser->name());
    }

    /**
     * @test
     */
    public function it_can_use_custom_aggregate_root_decorator()
    {
        $mock = $this->createMock(AggregateRootDecorator::class);

        $translator = new AggregateTranslator();
        $translator->setAggregateRootDecorator($mock);

        $this->assertSame($mock, $translator->getAggregateRootDecorator());
    }

    protected function resetRepository()
    {
        $this->repository = new AggregateRepository(
            $this->eventStore,
            AggregateType::fromAggregateRootClass('ProophTest\EventSourcing\Mock\User'),
            new AggregateTranslator()
        );
    }
}
