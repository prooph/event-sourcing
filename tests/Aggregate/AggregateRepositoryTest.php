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

use PHPUnit\Framework\TestCase;
use Prooph\EventSourcing\Aggregate\AggregateRepository;
use Prooph\EventSourcing\Aggregate\AggregateRootTranslator;
use Prooph\EventSourcing\Aggregate\AggregateType;
use Prooph\EventSourcing\MessageTransformer;
use Prooph\EventStoreClient\EventStoreSyncConnection;
use ProophTest\EventSourcing\Helper\Connection;
use ProophTest\EventSourcing\Mock\User;
use ProophTest\EventSourcing\Mock\UserCreated;

class AggregateRepositoryTest extends TestCase
{
    /**
     * @var EventStoreSyncConnection
     */
    protected $eventStoreConnection;

    /**
     * @var AggregateRepository
     */
    protected $repository;

    protected function setUp(): void
    {
        $this->eventStoreConnection = Connection::createSync();
        $this->eventStoreConnection->connect();

        $this->repository = new AggregateRepository(
            $this->eventStoreConnection,
            new AggregateType(['user' => User::class]),
            new AggregateRootTranslator(),
            new MessageTransformer([
                'user_created' => UserCreated::class,
            ]),
            true
        );
    }

    /**
     * @test
     */
    public function it_adds_a_new_aggregate(): void
    {
        $user = User::nameNew('John Doe');

        $this->repository->saveAggregateRoot($user);

        $fetchedUser = $this->repository->getAggregateRoot(
            $user->id()
        );

        $this->assertInstanceOf(User::class, $fetchedUser);

        $this->assertNotSame($user, $fetchedUser);

        $this->assertEquals('John Doe', $fetchedUser->name());
    }

    /**
     * @test
     */
    public function it_returns_early_on_get_aggregate_root_when_there_are_no_stream_events(): void
    {
        $this->assertNull($this->repository->getAggregateRoot('something'));
    }

    /**
     * @test
     * @doesNotPerformAssertions
     * Test for https://github.com/prooph/event-sourcing/issues/42
     */
    public function it_does_not_throw_an_exception_if_no_pending_event_is_present(): void
    {
        $user = User::nameNew('John Doe');
        $this->repository->saveAggregateRoot($user);
        $this->repository->saveAggregateRoot($user);
    }
}
