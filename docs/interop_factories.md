# Interop Factories

Instead of providing a module, a bundle, a bridge or similar framework integration prooph/event-store ships with `interop factories`.

## Factory-Driven Creation

The concept behind these factories (see `src/Container` folder) is simple but powerful. It allows us to provide you with bootstrapping logic for the event store and related components
without the need to rely on a specific framework. However, the factories have three requirements.

### Requirements

1. Your Inversion of Control container must implement the [PSR Container interface](https://github.com/php-fig/container).
2. [interop-config](https://github.com/sandrokeil/interop-config) must be installed
3. The application configuration should be registered with the service id `config` in the container.

*Note: Don't worry, if your environment doesn't provide these requirements, you can
always bootstrap the components by hand. Just look at the factories for inspiration in this case.*

### AggregateRepositoryFactory

To ease set up of repositories for your aggregate roots prooph/event-store also ships with a `Prooph\EventStore\Container\Aggregate\AbstractAggregateRepositoryFactory`.
It is an abstract class implementing the `container-interop RequiresContainerId` interface. The `containerId` method
itself is not implemented in the abstract class. You have to extend it and provide the container id because each
aggregate repository needs a slightly different configuration and therefore needs its own config key.

*Note: You can have a look at the `ProophTest\EventStore\Mock\RepositoryMockFactory`. It sounds more complex than it is.*

Let's say we have a repository factory for a User aggregate root. We use `user_repository` as container id and add this
configuration to our application configuration:

```php
[
    'prooph' => [
        'event_sourcing' => [
            'aggregate_repository' => [
                'user_repository' => [ //<-- here the container id is referenced
                    'event_store_connection' => 'es_connection', // <-- service name of your event store connection
                    'repository_class' => MyUserRepository::class, //<-- FQCN of the repository responsible for the aggregate root
                    'aggregate_type' => [
                        'user' => MyUser::class, //<-- The aggregate root FQCN the repository is responsible for
                    ],
                    'aggregate_translator' => 'user_translator', //<-- The aggregate translator must be available as service in the container
                    'message_map' => [
                        'user-registered' => UserRegistered::class,
                        'user-renamed' => UserRenamed::class,
                    ],
                    'use_optimistic_concurrency_by_default' => true,
                ],
            ],
        ],
    ],
]
```
