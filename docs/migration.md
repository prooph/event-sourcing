# Migration from v4 to v5

## Aggregate Repository

The aggregate repository has a new method `saveAggregateRoot($aggregateRoot)` which replaces the
`addAggregateRoot($aggregateRoot)` method. Additionally this method has to be called everytime you change an aggregate
root, so if you're working with the command bus, you have to update all your handlers and call `saveAggregateRoot` everywhere.

Reason: In the past the aggregate root was automatically updated with the aggregate repository due to the
`EventStore::commit` event hook. The event store does not implement the `commit` method in all cases now, only the ones
implementing the `Prooph\EventStore\TransactionalEventStore` interface. Also not all event stores are needed to trigger
action events. Only the ones that are wrapped by `Prooph\EventStore\ActionEventEmitterEventStore` or `Prooph\EventStore\TransactionalActionEventEmitterEventStore`.
In order for the repositories to work in all scenarios (where you can also change the used event store implementation in your app)
this behaviour has been removed and you always have to call `saveAggregateRoot($aggregateRoot)` on the aggregate repository yourself.

## Moved classes

The classes `Prooph\EventStore\Aggregate\*` are moved from the event-store repository to the event-sourcing repository.
Hence the new class name `Prooph\EventSourcing\Aggregate\*`. This includes the `AggregateRepository`, `AggregateTranslator`,
`AggregateType`, `AggregateTypeProvider` and `ConfigurableAggregateTranslator` as well as some exception classes.

Same goes for `Prooph\EventStore\Snapshot\*` classes.

Reason:

The event-store should not know anything about event sourcing at all, it's only a mechanism to store a stream of events.
Therefore all those classes are moved.

## Snapshot Store

The snapshot store is now a simple interface, see `Prooph\EventSourcing\Snapshot\SnapshotStore`. The adapters are all removed
and replaced by different snapshot store implementation, f.e. `Prooph\EventSourcing\Snapshot\InMemorySnapshotStore`.
  
## Aggregate Root

The method `apply` is now an abstract protected method. The old implementation was:

```php
protected function apply(AggregateChanged $e)
{
    $handler = $this->determineEventHandlerMethodFor($e);
    if (! method_exists($this, $handler)) {
        throw new \RuntimeException(sprintf(
            'Missing event handler method %s for aggregate root %s',
            $handler,
            get_class($this)
        ));
    }
    $this->{$handler}($e);
}

protected function determineEventHandlerMethodFor(AggregateChanged $e)
{
    return 'when' . implode(array_slice(explode('\\', get_class($e)), -1));
}
```

and is now replaced by simply:

```php
abstract protected function apply(AggregateChanged $e): void;
```

If you want to old behaviour back, you have to implement this yourself.

Reason: It's much more performant to have the apply method implemented like:

```php
protected function apply(AggregateChanged $e): void
{
    if ($e instanceof Foo) {
        // do something
    } elseif ($e instance of Bar) {
        // do something else
    } else {
        throw new \DomainException('Unknown event applied');
    }
}

```

It's up to you which you prefer and hence there is no default implementation given anymore.
