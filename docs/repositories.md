# Working with Repositories

Repositories typically connect your domain model with the persistence layer (part of the infrastructure).
Following DDD suggestions your domain model should be database agnostic.
An event store is of course some kind of database so you are likely looking for a third-party event store that gets out of your way.

*The good news is:* **You've found one!**

But you need to get familiar with the concept. So you're pleased to read this document and follow the example.
Afterwards you should be able to integrate `prooph/event-store` into your infrastructure without coupling it with your model.

## Event Sourced Aggregates

We assume that you want to work with event sourced aggregates. If you are not sure what we are talking about
please refer to the great educational project [Buttercup.Protects](http://buttercup-php.github.io/protects/) by Mathias Verraes.
prooph/event-store does not include base classes or traits to add event sourced capabilities to your aggregates.

Sounds bad? It isn't!

It is your job to write something like `Buttercup.Protects` for your model. Don't be lazy in this case.

The event store doesn't know anything about aggregates. It is just interested in `Prooph\Common\Messaging\Message events`.
These events are organized in `Prooph\EventStore\Stream`s.
A repository is responsible for extracting pending events from aggregates and putting them in the correct stream.
And the repository must also be able to load persisted events from a stream and reconstitute an aggregate.
To provide this functionality the repository makes use of various helper classes explained below.

## AggregateType
Each repository is responsible for one `\Prooph\EventSourcing\Aggregate\AggregateType`.

## AggregateTranslator

To achieve 100% decoupling between layers and/or contexts you can make use of translation adapters.
For prooph/event-store such a translation adapter is called a `Prooph\EventSourcing\Aggregate\AggregateTranslator`.

The interface requires you to implement 5 methods:

- extractAggregateId
- extractAggregateVersion
- extractPendingStreamEvents
- reconstituteAggregateFromHistory
- replayStreamEvents

To make your life easier prooph/event-sourcing ships with a `\Prooph\EventSourcing\Aggregate\AggregateTranslator` which implements the interface.

## Snapshot Store

A repository can be set up with a snapshot store to speed up loading of aggregates.

You need to install [Prooph SnapshotStore](https://github.com/prooph/snapshot-store) and a persistable implementation of it,
like [pdo-snapshot-store](https://github.com/prooph/pdo-snapshot-store/) or [mongodb-snapshot-store](https://github.com/prooph/mongodb-snapshot-store/).

## Event Streams

An event stream can be compared with a table in a relational database (and in case of the pdo-event-store it is a table).
By default the repository puts all events of all aggregates (no matter the type) in a single stream called **event_stream**.
If you wish to use another name, you can pass a custom `Prooph\EventStore\StreamName` to the repository.
This is especially useful when you want to have an event stream per aggregate type, for example store all user related events
in a `user_stream`.

The repository can also be configured to create a new stream for each new aggregate instance. You'll need to turn the last
constructor parameter `oneStreamPerAggregate` to true to enable the mode.
If the mode is enabled the repository builds a unique stream name for each aggregate by using the `AggregateType` and append
the `aggregateId` of the aggregate. The stream name for a new `Acme\User` with id `123` would look like this: `Acme\User-123`.

Depending on the event store implementation used the stream name is maybe modified by the implementation to replace or removed non supported characters.
Check your event store implemtation of choice for details. You can also override `AggregateRepository::determineStreamName` to apply a custom logic
for building the stream name.

## Wiring It Together

Best way to see a repository in action is by looking at the `\ProophTest\EventSourcing\Aggregate\AggregateRepositoryTest`.

### Set Up

```php
$this->repository = new AggregateRepository(
    $this->eventStore,
    AggregateType::fromAggregateRootClass('ProophTest\EventSourcing\Mock\User'),
    new AggregateTranslator()
);

$this->eventStore->create(new Stream(new StreamName('event_stream'), new ArrayIterator()));
```

Notice the injected dependencies! Snapshot store, stream name and stream mode are optional and not injected for all tests.
Therefore stream name defaults to **event_stream** and the repository appends all events to this stream.
For the test cases we also create the stream on every run. In a real application you need to do this only once.

```php
/**
 * @test
 */
public function it_adds_a_new_aggregate(): void
{
    $user = User::create('John Doe', 'contact@prooph.de');

    $this->repository->saveAggregateRoot($user);

    $fetchedUser = $this->repository->getAggregateRoot(
        $user->getId()->toString()
    );

    $this->assertInstanceOf('ProophTest\EventStore\Mock\User', $fetchedUser);

    $this->assertNotSame($user, $fetchedUser);

    $this->assertEquals('John Doe', $fetchedUser->name());

    $this->assertEquals('contact@prooph.de', $fetchedUser->email());
}
```

In the first test case you can see how an aggregate (the user entity in this case) can be added to the repository.

```php
/**
 * @test
 */
public function it_tracks_changes_of_aggregate(): void
{
    $user = User::create('John Doe', 'contact@prooph.de');

    $this->repository->saveAggregateRoot($user);

    $fetchedUser = $this->repository->getAggregateRoot(
        $user->getId()->toString()
    );

    $this->assertNotSame($user, $fetchedUser);

    $fetchedUser->changeName('Max Mustermann');
    
    $this->repository->saveAggregateRoot($fetchedUser);

    $fetchedUser2 = $this->repository->getAggregateRoot(
        $user->getId()->toString()
    );

    $this->assertNotSame($fetchedUser, $fetchedUser2);

    $this->assertEquals('Max Mustermann', $fetchedUser2->name());
}
```

Here we first add the user, then load it with the help of the repository and finally we change the user entity.
The change causes a `UserNameChanged` event.

**Note** the identity map is cleared after each transaction commit. You may notice the `assertNotSame` checks in the test.
The repository keeps an aggregate only in memory as long as the transaction is active. Otherwise multiple long-running
processes dealing with the same aggregate would run into concurrency issues very fast.

The test case has some more tests including snapshot usage and working with different stream names / strategies.
Just browse through the test methods for details.

You can also disable the identity map by passing that option to the constructor (provided interop-factory can do this for you as well).

## Aggregate Type Mapping

It's possible to map an aggregate type `user` to an aggregate root class like `My\Model\User`. To do that, add the
aggregate type mapping to your repository and use the provided aggregate type. The aggregate type mapping is implemented
in the AggregateType class like this:

```php
$aggregateType = AggregateType::fromMapping(['user' => MyUser::class]);
```

Example configuration:

```php
[
    'prooph' => [
        'event_sourcing' => [
            'aggregate_repository' => [
                'user_repository' => [
                    'repository_class' => MyUserRepository::class,
                    'aggregate_type' => [
                        'user' => MyUser::class, // <- custom name to class mapping 
                    ],
                    'aggregate_translator' => 'user_translator',
                ],
            ],
        ],
    ],
]
```

## Loading of thousands aggregates

If you need to load thousands of aggregates for reading only, your memory can be exhausted, because the 
`AggregateRepository` uses an identity map (if it's not disabled). So every loaded aggregate is stored there,
unless a commit is executed. If you have a read only process, you should consider to clear the identity map
at some time. This can be done by calling `clearIdentityMap()`.

```php
$thousandsOfAggregateIds = [ // lots of ids here ];
$number = 0;

foreach ($thousandsOfAggregateIds as $aggregateId) {
    $aggregate = $this->repository->getAggregateRoot($aggregateId);
    $number++;

    // do something with the aggregate data e.g. build read model

    // clear on every 500th aggregate
    if (0 === $number % 500) {
        $this->repository->clearIdentityMap();
    }
}
```
