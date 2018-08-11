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
            switch (\get_class($event)) {
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
        protected $messageName = 'user_was_created';

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
        protected $messageName = 'user_was_renamed';

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
    use My\Model\UserWasCreated;
    use My\Model\UserWasRenamed;
    use Prooph\Common\Messaging\MappedMessageFactory;
    use Prooph\Common\Messaging\NoOpMessageConverter;
    use Prooph\EventSourcing\Aggregate\AggregateRepository;
    use Prooph\EventSourcing\Aggregate\AggregateRootTranslator;
    use Prooph\EventSourcing\Aggregate\AggregateType;
    use Prooph\EventSourcing\MessageTransformer;
    use Prooph\EventStoreClient\EventStoreSyncConnection;
    use Ramsey\Uuid\Uuid;

    class UserRepositoryImpl extends AggregateRepository implements UserRepository
    {
        public function __construct(EventStoreSyncConnection $eventStoreClient)
        {
            //We inject a Prooph\EventSourcing\Aggregate\AggregateTranslator that can handle our AggregateRoots
            parent::__construct(
                $eventStoreClient,
                new AggregateType(['user' => 'My\Model\User']),
                new AggregateRootTranslator(),
                new MessageTransformer(
                    new MappedMessageFactory([
                        'user_was_created' => UserWasCreated::class,
                        'user_was_renamed' => UserWasRenamed::class,
                    ]),
                    new NoOpMessageConverter()
                ),
                true
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
    use Prooph\EventStoreClient\EventStoreConnectionBuilder;
    use Prooph\EventStoreClient\IpEndPoint;

    $eventStoreConnection = EventStoreConnectionBuilder::createFromIpEndPoint(new IpEndPoint('127.0.0.1', 1113));
    $eventStoreConnection->connect();

    //Now we set up our user repository and inject the EventStore
    //Normally this should be done in an IoC-Container and the receiver of the repository should require My\Model\UserRepository
    $userRepository = new UserRepositoryImpl($eventStoreConnection);

    $user = User::nameNew('John Doe');

    $userRepository->save($user);

    $userId = $user->userId();

    echo 'created new user with name "John Doe"' . PHP_EOL;
    unset($user);

    //Ok, great. Now let's see how we can grab the user from the repository and change the name

    //The repository automatically tracks changes of the user...
    $loadedUser = $userRepository->get($userId);

    $loadedUser->changeName('Max Mustermann');

    $userRepository->save($loadedUser);

    echo 'updated user name to "Max Mustermann"' . PHP_EOL;
}
