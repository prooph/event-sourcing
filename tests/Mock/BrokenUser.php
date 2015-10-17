<?php
/*
 * This file is part of the prooph/event-store.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 08/26/15 - 19:58
 */

namespace ProophTest\EventSourcing\Mock;

use Prooph\EventSourcing\AggregateChanged;
use Prooph\EventSourcing\AggregateRoot;
use Rhumsaa\Uuid\Uuid;

/**
 * Class BrokenUser
 *
 * @package ProophTest\EventSourcing\Mock
 * @author Alexander Miertsch <contact@prooph.de>
 */
class BrokenUser extends AggregateRoot
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
     * @return string
     */
    public function name()
    {
        return $this->name;
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
