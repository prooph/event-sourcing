# Snapshots

One of the counter-arguments against Event-Sourcing you might heard about is that replaying events takes too much time.

Replaying 20 events for an aggregate is fast, 50 is ok, but 100 events and more become slow depending on the data of the events and the operations needed to replay them.
A DDD rule of thumb says aggregates should be kept small. Keeping that in mind you should be fine with 50 events per aggregate
in most cases.

## But my aggregates record tons of events!

If aggregate reconstitution gets slow you can add an additional layer to the system which
is capable of providing aggregate snapshots.

Choose one of the `Prooph\*SnapshotStore` to take snapshots.
Inject the snapshot store into an aggregate repository and the repository will use the snapshot store to speed up
aggregate loading.

Our example application [proophessor-do](https://github.com/prooph/proophessor-do) contains a snapshotting tutorial.

*Note: All SnapshotStores ship with interop factories to ease set up.*

## Creating snapshot projections

Basically there are two possibilities:

First, if you are using a single stream or one stream per aggregate type, in this case you need to
create a projection from that stream.

Second, if you are using one stream per aggregate, you need to create the stream from the category,
when you have no category stream created (see: [StandardProjection](https://github.com/prooph/standard-projections/)).

All you have to do to create a snapshot projection is to create a small and simple script like this:

```php
$projection = $eventStore->createReadModelProjection(
    'user_snapshots',
    new \Prooph\EventSourcing\Aggregate\SnapshotReadModel(
        $aggregateRepository,
        $aggregateTranslator,
        $snapshotStore
    )
);

$projection
    ->fromStream('user_stream')
    ->whenAny(function ($state, Message $event): void {
        $this->readModel()->stack('replay', $event);
    })
    ->run();
```

or in case you need to create the projection from category:

```php
$projection = $eventStore->createReadModelProjection(
    'user_snapshots',
    new \Prooph\EventSourcing\Aggregate\SnapshotReadModel(
        $aggregateRepository,
        $aggregateTranslator,
        $snapshotStore
    )
);

$projection
    ->fromCategory('user')
    ->whenAny(function ($state, Message $event): void {
        $this->readModel()->stack('replay', $event);
    })
    ->run();
```
