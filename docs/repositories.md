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

The event store doesn't know anything about aggregates.
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

## Aggregate Type Mapping

Every aggregate type has a mapping of aggregate type to aggregate root class.
The first aggregate type will be used as stream-category.

F.e.

```php
[
    'user' => User::class,
    'admin' => Admin::class,
]
```

Your aggregate type is here 'user' or 'admin' (we are using here inheritance aggregate roots) and your stream category is 'user'.

## Event Streams

An event stream is your stream category + "-" + the aggregate id.
F.e. "user-30a00d47-70e3-4ee2-b8fb-1bdd160259d7".

## Wiring It Together

Best way to see a repository in action is by looking at the `\ProophTest\EventSourcing\Aggregate\AggregateRepositoryTest`.
