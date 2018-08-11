<?php
/**
 * This file is part of the prooph/event-sourcing.
 * (c) 2014-2018 prooph software GmbH <contact@prooph.de>
 * (c) 2015-2018 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace ProophTest\EventSourcing\Aggregate;

use ArrayIterator;
use PHPUnit\Framework\TestCase;
use Prooph\EventSourcing\Aggregate\AggregateRepository;
use Prooph\EventSourcing\Aggregate\AggregateRootDecorator;
use Prooph\EventSourcing\Aggregate\AggregateRootTranslator;
use Prooph\EventSourcing\Aggregate\AggregateType;
use Prooph\EventSourcing\MessageTransformer;
use Prooph\EventStoreClient\EventStoreSyncConnection;
use ProophTest\EventSourcing\Helper\Connection;
use ProophTest\EventSourcing\Mock\User;
use ProophTest\EventSourcing\Mock\UserCreated;
use ProophTest\EventSourcing\Mock\UserNameChanged;

class AggregateRootTranslatorTest extends TestCase
{
    /**
     * @var EventStoreSyncConnection
     */
    protected $eventStoreClient;

    /**
     * @var AggregateRepository
     */
    protected $repository;

    protected function setUp(): void
    {
        $this->eventStoreClient = Connection::createSync();
        $this->eventStoreClient->connect();

        $this->repository = new AggregateRepository(
            $this->eventStoreClient,
            new AggregateType(['user' => User::class]),
            new AggregateRootTranslator(),
            new MessageTransformer([
                'user_created' => UserCreated::class,
                'user_name_changed' => UserNameChanged::class,
            ]),
            true
        );
    }

    /** @test */
    public function it_translates_aggregate_back_and_forth(): User
    {
        $user = User::nameNew('John Doe');

        $this->repository->saveAggregateRoot($user);

        //Simulate a normal program flow by fetching the AR before modifying it
        $user = $this->repository->getAggregateRoot($user->id());

        $user->changeName('Max Mustermann');

        $this->repository->saveAggregateRoot($user);

        $loadedUser = $this->repository->getAggregateRoot($user->id());

        $this->assertEquals('Max Mustermann', $loadedUser->name());

        return $loadedUser;
    }

    /**
     * @test
     * @depends it_translates_aggregate_back_and_forth
     * @param User $loadedUser
     */
    public function it_applies_stream_events(User $loadedUser): void
    {
        $newName = 'Jane Doe';

        $translator = new AggregateRootTranslator();
        $translator->replayStreamEvents($loadedUser, new ArrayIterator([UserNameChanged::occur($loadedUser->id(), [
            'username' => $newName,
        ])]));

        $this->assertEquals($newName, $loadedUser->name());
    }

    /** @test */
    public function it_can_use_custom_aggregate_root_decorator(): void
    {
        $mock = $this->createMock(AggregateRootDecorator::class);

        $translator = new AggregateRootTranslator();
        $translator->setAggregateRootDecorator($mock);

        $this->assertSame($mock, $translator->getAggregateRootDecorator());
    }
}
