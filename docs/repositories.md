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

The interface requires you to implement 6 methods:

- extractExpectedVersion
- setExpectedVersion
- extractAggregateId
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

## Aggregate Type Mapping

It's possible to map an aggregate type `user` to an aggregate root class like `My\Model\User`. To do that, add the
aggregate type mapping to your repository and use the provided aggregate type. The aggregate type mapping is implemented
in the AggregateType class like this:

```php
$aggregateType = new AggregateType(['user' => MyUser::class]);
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
