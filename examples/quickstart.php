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

        /**
         * @param $newName
         */
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
         * Each applied event needs a corresponding handler method.
         *
         * The naming convention is: when[:ShortEventName]
         */
        protected function whenUserWasCreated(UserWasCreated $event): void
        {
            //Simply assign the event payload to the appropriate properties
            $this->uuid = Uuid::fromString($event->aggregateId());
            $this->name = $event->username();
        }

        protected function whenUserWasRenamed(UserWasRenamed $event): void
        {
            $this->name = $event->newName();
        }

        /**
         * Every AR needs a hidden method that returns the identifier of the AR as a string
         */
        protected function aggregateId(): string
        {
            return $this->uuid->toString();
        }
    }

    /**
     * Class UserWasCreated
     *
     * ProophEventSourcing domain events are of the type AggregateChanged
     *
     * @package My\Model
     * @author Alexander Miertsch <kontakt@codeliner.ws>
     */
    class UserWasCreated extends AggregateChanged
    {
        public function username(): string
        {
            return $this->payload['name'];
        }
    }

    /**
     * Class UserWasRenamed
     *
     * ProophEventSourcing domain events are of the type AggregateChanged
     *
     * @package My\Model
     * @author Alexander Miertsch <kontakt@codeliner.ws>
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
     * Interface UserRepository
     *
     * Simple interface for a user repository
     *
     * @package My\Model
     * @author Alexander Miertsch <kontakt@codeliner.ws>
     */
    interface UserRepository
    {
        public function add(User $user): void;

        public function get(Uuid $uuid): User;
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

    /**
     * Class UserRepositoryImpl extends Prooph\EventSourcing\Aggregate\AggregateRepository and implements My\Model\UserRepository
     *
     * @package My\Infrastructure
     * @author Alexander Miertsch <kontakt@codeliner.ws>
     */
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

        /**
         * @param User $user
         */
        public function add(User $user): void
        {
            $this->addAggregateRoot($user);
        }

        public function get(Uuid $uuid): User
        {
            return $this->getAggregateRoot($uuid->toString());
        }
    }
}

namespace {
    //Set up an EventStore with an InMemoryAdapter (Only useful for testing, persistent adapters for ProophEventStore are available)
    use My\Infrastructure\UserRepositoryImpl;
    use My\Model\User;
    use Prooph\Common\Event\ActionEvent;
    use Prooph\Common\Event\ProophActionEventEmitter;
    use Prooph\EventStore\InMemoryEventStore;

    $eventStore = new InMemoryEventStore(new ProophActionEventEmitter());

    //Now we set up our user repository and inject the EventStore
    //Normally this should be done in an IoC-Container and the receiver of the repository should require My\Model\UserRepository
    $userRepository = new UserRepositoryImpl($eventStore);

    //Ok lets start a new transaction and create a user
    $eventStore->beginTransaction();

    $user = User::nameNew('John Doe');

    $userRepository->add($user);

    //Before we commit the transaction let's attach a listener to check that the UserWasCreated event is published after commit
    $eventStore->getActionEventEmitter()->attachListener('commit.post', function (ActionEvent $event): void {
        foreach ($event->getParam('recordedEvents', new \ArrayIterator()) as $streamEvent) {
            echo sprintf(
                'Event with name %s was recorded. It occurred on %s UTC /// ',
                $streamEvent->messageName(),
                $streamEvent->createdAt()->format('Y-m-d H:i:s')
            );
        }
    });

    $eventStore->commit();

    $userId = $user->userId();

    unset($user);

    //Ok, great. Now let's see how we can grab the user from the repository and change the name

    //First we need to start a new transaction
    $eventStore->beginTransaction();

    //The repository automatically tracks changes of the user...
    $loadedUser = $userRepository->get($userId);

    $loadedUser->changeName('Max Mustermann');

    //... so we only need to commit the transaction and the UserWasRenamed event should be recorded
    //(check output of the previously attached listener)
    $eventStore->commit();
}
