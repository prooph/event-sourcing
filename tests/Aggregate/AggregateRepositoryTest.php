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

namespace ProophTest\EventSourcing\Aggregate;

use Prooph\Common\Event\ActionEvent;
use Prooph\EventSourcing\Aggregate\AggregateRepository;
use Prooph\EventSourcing\Aggregate\AggregateType;
use Prooph\EventSourcing\Aggregate\ConfigurableAggregateTranslator;
use Prooph\EventSourcing\Aggregate\Exception\AggregateTypeException;
use Prooph\EventSourcing\Aggregate\Exception\InvalidArgumentException;
use Prooph\EventSourcing\Snapshot\InMemorySnapshotStore;
use Prooph\EventSourcing\Snapshot\Snapshot;
use Prooph\EventSourcing\Snapshot\SnapshotStore;
use Prooph\EventStore\ActionEventEmitterEventStore;
use Prooph\EventStore\EventStore;
use Prooph\EventStore\Stream;
use Prooph\EventStore\StreamName;
use ProophTest\EventStore\Mock\User;
use ProophTest\EventStore\Mock\UserCreated;
use ProophTest\EventStore\Mock\UsernameChanged;
use ProophTest\EventStore\TestCase;
use Prophecy\Argument;

class AggregateRepositoryTest extends TestCase
{
    /**
     * @var AggregateRepository
     */
    private $repository;

    /**
     * @var SnapshotStore
     */
    private $snapshotStore;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new AggregateRepository(
            $this->eventStore,
            AggregateType::fromAggregateRootClass(User::class),
            new ConfigurableAggregateTranslator()
        );

        $this->eventStore->beginTransaction();

        $this->eventStore->create(new Stream(new StreamName('event_stream'), new \ArrayIterator()));

        $this->eventStore->commit();
    }

    /**
     * @test
     */
    public function it_adds_a_new_aggregate(): void
    {
        $this->eventStore->beginTransaction();

        $user = User::create('John Doe', 'contact@prooph.de');

        $this->repository->saveAggregateRoot($user);

        $this->eventStore->commit();

        $fetchedUser = $this->repository->getAggregateRoot(
            $user->getId()->toString()
        );

        $this->assertInstanceOf(User::class, $fetchedUser);

        $this->assertNotSame($user, $fetchedUser);

        $this->assertEquals('John Doe', $fetchedUser->name());

        $this->assertEquals('contact@prooph.de', $fetchedUser->email());
    }

    /**
     * @test
     */
    public function it_tracks_changes_of_aggregate_but_always_returns_a_fresh_instance_on_load(): void
    {
        $this->eventStore->beginTransaction();

        $user = User::create('John Doe', 'contact@prooph.de');

        $this->repository->saveAggregateRoot($user);

        $this->eventStore->commit();

        $this->eventStore->beginTransaction();

        $fetchedUser = $this->repository->getAggregateRoot(
            $user->getId()->toString()
        );

        $this->assertNotSame($user, $fetchedUser);

        $fetchedUser->changeName('Max Mustermann');

        $this->eventStore->commit();

        $fetchedUser2 = $this->repository->getAggregateRoot(
            $user->getId()->toString()
        );

        $this->assertNotSame($fetchedUser, $fetchedUser2);

        $this->assertEquals('Max Mustermann', $fetchedUser2->name());
    }

    /**
     * @test
     * Test for https://github.com/prooph/event-store/issues/99
     */
    public function it_does_not_interfere_with_other_aggregate_roots_in_pending_events_index(): void
    {
        $this->eventStore->beginTransaction();

        $user = User::create('John Doe', 'contact@prooph.de');

        $this->repository->saveAggregateRoot($user);

        $user2 = User::create('Max Mustermann', 'some@mail.com');

        $this->repository->saveAggregateRoot($user2);

        $this->eventStore->commit();

        $this->eventStore->beginTransaction();

        //Fetch users from repository to simulate a normal program flow
        $user = $this->repository->getAggregateRoot($user->getId()->toString());
        $user2 = $this->repository->getAggregateRoot($user2->getId()->toString());

        $user->changeName('Daniel Doe');
        $user2->changeName('Jens Mustermann');

        $this->eventStore->commit();

        $fetchedUser1 = $this->repository->getAggregateRoot(
            $user->getId()->toString()
        );

        $fetchedUser2 = $this->repository->getAggregateRoot(
            $user2->getId()->toString()
        );

        $this->assertEquals('Daniel Doe', $fetchedUser1->name());
        $this->assertEquals('Jens Mustermann', $fetchedUser2->name());
    }

    /**
     * @test
     */
    public function it_asserts_correct_aggregate_type(): void
    {
        $this->expectException(AggregateTypeException::class);
        $this->expectExceptionMessage('Aggregate root must be an object but type of string given');

        $this->repository->saveAggregateRoot('invalid');
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
     */
    public function it_loads_the_entire_stream_if_one_stream_per_aggregate_is_enabled(): void
    {
        $eventStore = $this->prophesize(ActionEventEmitterEventStore::class);
        $eventStore
            ->load(
                Argument::that(function (StreamName $streamName) {
                    return $streamName->toString() === User::class . '-123';
                }),
                1,
                null,
                null
            )->willReturn(new Stream(new StreamName(User::class . '-123'), new \ArrayIterator()));

        $repository = new AggregateRepository(
            $eventStore->reveal(),
            AggregateType::fromAggregateRootClass(User::class),
            new ConfigurableAggregateTranslator(),
            null,
            null,
            true
        );

        $repository->getAggregateRoot('123');
    }

    /**
     * @test
     */
    public function it_uses_snapshot_store(): void
    {
        $this->prepareSnapshotStoreAggregateRepository();

        $this->eventStore->beginTransaction();

        $user = User::create('John Doe', 'contact@prooph.de');

        $this->repository->saveAggregateRoot($user);

        $this->eventStore->commit();

        $now = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        $snapshot = new Snapshot(
            AggregateType::fromAggregateRootClass(User::class),
            $user->getId()->toString(),
            $user,
            1,
            $now
        );

        // short getter assertion
        $this->assertSame($now, $snapshot->createdAt());

        $this->snapshotStore->save($snapshot);

        $loadedEvents = [];

        $this->eventStore->getActionEventEmitter()->attachListener(
            'load',
            function (ActionEvent $event) use (&$loadedEvents) {
                foreach ($event->getParam('streamEvents', []) as $streamEvent) {
                    $loadedEvents[] = $streamEvent;
                }
            },
            -1000
        );

        $this->repository->getAggregateRoot(
            $user->getId()->toString()
        );

        $this->assertEmpty($loadedEvents);
    }

    /**
     * @test
     */
    public function it_uses_snapshot_store_with_one_stream_per_aggregate(): void
    {
        $this->snapshotStore = new InMemorySnapshotStore();

        $this->repository = new AggregateRepository(
            $this->eventStore,
            AggregateType::fromAggregateRootClass(User::class),
            new ConfigurableAggregateTranslator(),
            $this->snapshotStore,
            null,
            true
        );

        $this->eventStore->beginTransaction();

        $user = User::create('John Doe', 'contact@prooph.de');

        $this->repository->saveAggregateRoot($user);

        $this->eventStore->commit();

        $now = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        $snapshot = new Snapshot(
            AggregateType::fromAggregateRootClass(User::class),
            $user->getId()->toString(),
            $user,
            1,
            $now
        );

        // short getter assertion
        $this->assertSame($now, $snapshot->createdAt());

        $this->snapshotStore->save($snapshot);

        $loadedEvents = [];

        $this->eventStore->getActionEventEmitter()->attachListener(
            'load',
            function (ActionEvent $event) use (&$loadedEvents) {
                foreach ($event->getParam('streamEvents', []) as $streamEvent) {
                    $loadedEvents[] = $streamEvent;
                }
            },
            -1000
        );

        $this->repository->getAggregateRoot(
            $user->getId()->toString()
        );

        $this->assertEmpty($loadedEvents);
    }

    /**
     * @test
     */
    public function it_uses_snapshot_store_while_snapshot_store_is_empty(): void
    {
        $this->prepareSnapshotStoreAggregateRepository();

        $this->eventStore->beginTransaction();

        $user = User::create('John Doe', 'contact@prooph.de');

        $this->repository->saveAggregateRoot($user);

        $this->eventStore->commit();

        $loadedEvents = [];

        $this->eventStore->getActionEventEmitter()->attachListener(
            'load',
            function (ActionEvent $event) use (&$loadedEvents) {
                $stream = $event->getParam('stream');
                if (! $stream) {
                    return;
                }
                foreach ($stream->streamEvents() as $streamEvent) {
                    $loadedEvents[] = $streamEvent;
                }
            },
            -1000
        );

        $this->repository->getAggregateRoot(
            $user->getId()->toString()
        );

        $this->assertCount(1, $loadedEvents);
        $this->assertInstanceOf(UserCreated::class, $loadedEvents[0]);
    }

    /**
     * @test
     */
    public function it_uses_snapshot_store_and_applies_pending_events(): void
    {
        $this->prepareSnapshotStoreAggregateRepository();

        $this->eventStore->beginTransaction();

        $user = User::create('John Doe', 'contact@prooph.de');

        $this->repository->saveAggregateRoot($user);

        $this->eventStore->commit();

        $snapshot = new Snapshot(
            AggregateType::fromAggregateRootClass(User::class),
            $user->getId()->toString(),
            $user,
            1,
            new \DateTimeImmutable('now', new \DateTimeZone('UTC'))
        );

        $this->snapshotStore->save($snapshot);

        $this->eventStore->beginTransaction();

        $fetchedUser = $this->repository->getAggregateRoot(
            $user->getId()->toString()
        );

        $fetchedUser->changeName('Max Mustermann');

        $this->eventStore->commit();

        $loadedEvents = [];

        $this->eventStore->getActionEventEmitter()->attachListener(
            'load',
            function (ActionEvent $event) use (&$loadedEvents) {
                $stream = $event->getParam('stream');
                if (! $stream) {
                    return;
                }
                foreach ($stream->streamEvents() as $streamEvent) {
                    $loadedEvents[] = $streamEvent;
                }
                $event->getParam('stream')->streamEvents()->rewind();
            },
            -1000
        );

        $this->repository->getAggregateRoot(
            $user->getId()->toString()
        );

        $this->assertCount(1, $loadedEvents);
        $this->assertInstanceOf(UsernameChanged::class, $loadedEvents[0]);
        $this->assertEquals(2, $this->repository->extractAggregateVersion($fetchedUser));
    }

    protected function prepareSnapshotStoreAggregateRepository()
    {
        parent::setUp();

        $this->snapshotStore = new InMemorySnapshotStore();

        $this->repository = new AggregateRepository(
            $this->eventStore,
            AggregateType::fromAggregateRootClass(User::class),
            new ConfigurableAggregateTranslator(),
            $this->snapshotStore
        );

        $this->eventStore->beginTransaction();

        $this->eventStore->create(new Stream(new StreamName('event_stream'), new \ArrayIterator()));

        $this->eventStore->commit();
    }

    /**
     * @test
     * Test for https://github.com/prooph/event-store/issues/179
     */
    public function it_tracks_changes_of_aggregate_but_returns_a_same_instance_within_transaction(): void
    {
        $this->eventStore->beginTransaction();

        $user = User::create('John Doe', 'contact@prooph.de');

        $this->repository->saveAggregateRoot($user);

        $this->eventStore->commit();

        $this->eventStore->beginTransaction();

        $fetchedUser1 = $this->repository->getAggregateRoot(
            $user->getId()->toString()
        );

        $fetchedUser2 = $this->repository->getAggregateRoot(
            $user->getId()->toString()
        );

        $this->assertSame($fetchedUser1, $fetchedUser2);

        $fetchedUser1->changeName('Max Mustermann');

        $this->assertSame($fetchedUser1, $fetchedUser2);

        $this->eventStore->commit();
    }

    /**
     * @test
     */
    public function it_clears_identity_map_manually(): void
    {
        $this->eventStore->beginTransaction();

        $user = User::create('John Doe', 'contact@prooph.de');

        $this->repository->saveAggregateRoot($user);

        $this->eventStore->commit();

        // fill identity map
        $this->repository->getAggregateRoot(
            $user->getId()->toString()
        );

        $reflectionClass = new \ReflectionClass($this->repository);

        $reflectionProperty = $reflectionClass->getProperty('identityMap');
        $reflectionProperty->setAccessible(true);

        self::assertCount(1, $reflectionProperty->getValue($this->repository));
        $this->repository->clearIdentityMap();
        self::assertCount(0, $reflectionProperty->getValue($this->repository));
    }

    /**
     * @test
     */
    public function it_used_provided_stream_name(): void
    {
        $this->repository = new AggregateRepository(
            $this->eventStore,
            AggregateType::fromAggregateRootClass(User::class),
            new ConfigurableAggregateTranslator(),
            null,
            new StreamName('foo')
        );

        $this->eventStore->create(new Stream(new StreamName('foo'), new \ArrayIterator()));

        $this->eventStore->beginTransaction();

        $user = User::create('John Doe', 'contact@prooph.de');

        $this->repository->saveAggregateRoot($user);

        $this->eventStore->commit();
    }
}
