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

use Generator;
use PHPUnit\Framework\TestCase;
use Prooph\EventSourcing\Aggregate\AggregateRootTranslator;
use Prooph\EventSourcing\Aggregate\AggregateType;
use Prooph\EventSourcing\Aggregate\AsyncAggregateRepository;
use Prooph\EventSourcing\MessageTransformer;
use Prooph\EventStoreClient\EventStoreAsyncConnection;
use ProophTest\EventSourcing\Helper\Connection;
use ProophTest\EventSourcing\Mock\User;
use ProophTest\EventSourcing\Mock\UserCreated;
use Throwable;
use function Amp\call;
use function Amp\Promise\wait;

class AsyncAggregateRepositoryTest extends TestCase
{
    /**
     * @var EventStoreAsyncConnection
     */
    protected $eventStoreConnection;

    /**
     * @var AsyncAggregateRepository
     */
    protected $repository;

    protected function setUp(): void
    {
        $this->eventStoreConnection = Connection::createAsync();

        $this->repository = new AsyncAggregateRepository(
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
     * @throws Throwable
     */
    public function it_adds_a_new_aggregate(): void
    {
        wait(call(function (): Generator {
            yield $this->eventStoreConnection->connectAsync();

            $user = User::nameNew('John Doe');

            yield $this->repository->saveAggregateRoot($user);

            $fetchedUser = yield $this->repository->getAggregateRoot(
                $user->id()
            );

            $this->assertInstanceOf(User::class, $fetchedUser);

            $this->assertNotSame($user, $fetchedUser);

            $this->assertEquals('John Doe', $fetchedUser->name());
        }));
    }

    /**
     * @test
     * @throws Throwable
     */
    public function it_returns_early_on_get_aggregate_root_when_there_are_no_stream_events(): void
    {
        wait(call(function (): Generator {
            yield $this->eventStoreConnection->connectAsync();

            $this->assertNull(yield $this->repository->getAggregateRoot('something'));
        }));
    }

    /**
     * @test
     * @doesNotPerformAssertions
     * @throws Throwable
     * Test for https://github.com/prooph/event-sourcing/issues/42
     */
    public function it_does_not_throw_an_exception_if_no_pending_event_is_present(): void
    {
        wait(call(function (): Generator {
            yield $this->eventStoreConnection->connectAsync();

            $user = User::nameNew('John Doe');
            yield $this->repository->saveAggregateRoot($user);
            yield $this->repository->saveAggregateRoot($user);
        }));
    }
}
