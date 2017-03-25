<?php
/**
 * This file is part of the prooph/event-sourcing.
 * (c) 2014-2017 prooph software GmbH <contact@prooph.de>
 * (c) 2015-2017 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace {
    require_once __DIR__ . '/../vendor/autoload.php';
}

namespace My\Model {
    use Assert\Assertion;
    use Prooph\EventSourcing\AggregateChanged;
    use Prooph\EventSourcing\AggregateRoot;
    use Ramsey\Uuid\Uuid;

    class User extends AggregateRoot
    {
        /**
         * @var Uuid
         */
        private $uuid;

        /**
         * @var string
         */
        private $name;

        /**
         * ARs should be created via static factory methods
         */
        public static function nameNew(string $username): User
        {
            //Perform assertions before raising a event
            Assertion::notEmpty($username);

            $uuid = Uuid::uuid4();

            //AggregateRoot::__construct is defined as protected so it can be called in a static factory of
            //an extending class
            $instance = new self();

            //Use AggregateRoot::recordThat method to apply a new Event
            $instance->recordThat(UserWasCreated::occur($uuid->toString(), ['name' => $username]));

            return $instance;
        }

        public function userId(): Uuid
        {
            return $this->uuid;
        }

        public function name(): string
        {
            return $this->name;
        }

        public function changeName(string $newName): void
        {
            Assertion::notEmpty($newName);

            if ($newName !== $this->name) {
                $this->recordThat(UserWasRenamed::occur(
                    $this->uuid->toString(),
                    ['new_name' => $newName, 'old_name' => $this->name]
                ));
            }
        }

        /**
         * Every AR needs a hidden method that returns the identifier of the AR as a string
         */
        protected function aggregateId(): string
        {
            return $this->uuid->toString();
        }

        protected function apply(AggregateChanged $event): void
        {
            switch (get_class($event)) {
                case UserWasCreated::class:
                    //Simply assign the event payload to the appropriate properties
                    $this->uuid = Uuid::fromString($event->aggregateId());
                    $this->name = $event->username();
                    break;
                case UserWasRenamed::class:
                    $this->name = $event->newName();
                    break;
            }
        }
    }

    /**
     * ProophEventSourcing domain events are of the type AggregateChanged
     */
    class UserWasCreated extends AggregateChanged
    {
        public function username(): string
        {
            return $this->payload['name'];
        }
    }

    /**
     * ProophEventSourcing domain events are of the type AggregateChanged
     */
    class UserWasRenamed extends AggregateChanged
    {
        public function newName(): string
        {
            return $this->payload['new_name'];
        }

        public function oldName(): string
        {
            return $this->payload['old_name'];
        }
    }

    /**
     * Simple interface for a user repository
     */
    interface UserRepository
    {
        public function save(User $user): void;

        public function get(Uuid $uuid): ?User;
    }
}

namespace My\Infrastructure {
    use My\Model\User;
    use My\Model\UserRepository;
    use Prooph\EventSourcing\Aggregate\AggregateRepository;
    use Prooph\EventSourcing\Aggregate\AggregateType;
    use Prooph\EventSourcing\EventStoreIntegration\AggregateTranslator;
    use Prooph\EventStore\EventStore;
    use Ramsey\Uuid\Uuid;

    class UserRepositoryImpl extends AggregateRepository implements UserRepository
    {
        public function __construct(EventStore $eventStore)
        {
            //We inject a Prooph\EventSourcing\EventStoreIntegration\AggregateTranslator that can handle our AggregateRoots
            parent::__construct(
                $eventStore,
                AggregateType::fromAggregateRootClass('My\Model\User'),
                new AggregateTranslator(),
                null, //We don't use a snapshot store in the example
                null, //Also a custom stream name is not required
                true //But we enable the "one-stream-per-aggregate" mode
            );
        }

        public function save(User $user): void
        {
            $this->saveAggregateRoot($user);
        }

        public function get(Uuid $uuid): ?User
        {
            return $this->getAggregateRoot($uuid->toString());
        }
    }
}

namespace {
    //Set up an EventStore with an InMemoryAdapter (Only useful for testing, persistent implementations of ProophEventStore are available)
    use My\Infrastructure\UserRepositoryImpl;
    use My\Model\User;
    use Prooph\Common\Event\ActionEvent;
    use Prooph\Common\Event\ProophActionEventEmitter;
    use Prooph\EventStore\InMemoryEventStore;
    use Prooph\EventStore\TransactionalActionEventEmitterEventStore;

    $eventStore = new TransactionalActionEventEmitterEventStore(
        new InMemoryEventStore(),
            new ProophActionEventEmitter()
    );

    //Now we set up our user repository and inject the EventStore
    //Normally this should be done in an IoC-Container and the receiver of the repository should require My\Model\UserRepository
    $userRepository = new UserRepositoryImpl($eventStore);

    //Ok lets start a new transaction and create a user
    $eventStore->beginTransaction();

    $user = User::nameNew('John Doe');

    //Before we save let's attach a listener to check that the UserWasCreated event is recorded
    $eventStore->attach(
        TransactionalActionEventEmitterEventStore::EVENT_CREATE,
        function (ActionEvent $event): void {
            foreach ($event->getParam('stream')->streamEvents() as $streamEvent) {
                echo sprintf(
                    'Event with name %s was recorded. It occurred on %s UTC /// ',
                    $streamEvent->messageName(),
                    $streamEvent->createdAt()->format('Y-m-d H:i:s')
                ) . PHP_EOL;
            }
        },
        -1000
    );

    $userRepository->save($user);

    //Let's make sure the transaction is written
    $eventStore->attach(
        TransactionalActionEventEmitterEventStore::EVENT_COMMIT,
        function (ActionEvent $event): void {
            echo 'Transaction commited' . PHP_EOL;
        },
        -1000
    );

    $eventStore->commit();

    $userId = $user->userId();

    unset($user);

    //Ok, great. Now let's see how we can grab the user from the repository and change the name

    //First we need to start a new transaction
    $eventStore->beginTransaction();

    //The repository automatically tracks changes of the user...
    $loadedUser = $userRepository->get($userId);

    $loadedUser->changeName('Max Mustermann');

    //Before we save let's attach a listener again on appendTo to check that the UserWasRenamed event is recorded
    $eventStore->attach(
        TransactionalActionEventEmitterEventStore::EVENT_APPEND_TO,
        function (ActionEvent $event): void {
            foreach ($event->getParam('streamEvents') as $streamEvent) {
                echo sprintf(
                        'Event with name %s was recorded. It occurred on %s UTC /// ',
                        $streamEvent->messageName(),
                        $streamEvent->createdAt()->format('Y-m-d H:i:s')
                    ) . PHP_EOL;
            }
        },
        -1000
    );

    $userRepository->save($loadedUser);

    //... so we only need to commit the transaction and the UserWasRenamed event should be recorded
    //(check output of the previously attached listener)
    $eventStore->commit();
}
