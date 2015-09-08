<?php
/*
 * This file is part of the prooph/event-store.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 04/18/14 - 00:04
 */

namespace Prooph\EventSourcingTest\Mock;

use Prooph\EventSourcing\AggregateChanged;
use Prooph\EventSourcing\AggregateRoot;
use Rhumsaa\Uuid\Uuid;

/**
 * Class User
 *
 * @package Prooph\EventStoreTest\Mock
 * @author Alexander Miertsch <contact@prooph.de>
 */
class User extends AggregateRoot
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    public static function nameNew($name)
    {
        $id = Uuid::uuid4()->toString();
        $instance = new self();

        $instance->recordThat(UserCreated::occur($id, ['id' => $id, 'name' => $name]));

        return $instance;
    }

    /**
     * @param AggregateChanged[] $historyEvents
     * @return User
     */
    public static function fromHistory(array $historyEvents)
    {
        return self::reconstituteFromHistory($historyEvents);
    }
    /**
     * @return string
     */
    public function id()
    {
        return $this->id;
    }

    /**
     * @param string $newName
     */
    public function changeName($newName)
    {
        $this->recordThat(UserNameChanged::occur($this->id, ['username' => $newName]));
    }

    /**
     * @return string
     */
    public function name()
    {
        return $this->name;
    }

    /**
     * @param UserCreated $event
     */
    protected function whenUserCreated(UserCreated $event)
    {
        $this->id = $event->userId();
        $this->name = $event->name();
    }

    /**
     * @param UserNameChanged $event
     */
    protected function whenUsernameChanged(UserNameChanged $event)
    {
        $this->name = $event->newUsername();
    }

    /**
     * @return \Prooph\EventSourcing\AggregateChanged[]
     */
    public function accessRecordedEvents()
    {
        return $this->popRecordedEvents();
    }

    /**
     * @return string representation of the unique identifier of the aggregate root
     */
    protected function aggregateId()
    {
        return $this->id();
    }
}
