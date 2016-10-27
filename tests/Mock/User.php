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

namespace ProophTest\EventSourcing\Mock;

use Prooph\EventSourcing\AggregateRoot;
use Ramsey\Uuid\Uuid;

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

    public static function nameNew(string $name): self
    {
        $id = Uuid::uuid4()->toString();
        $instance = new self();

        $instance->recordThat(UserCreated::occur($id, ['id' => $id, 'name' => $name]));

        return $instance;
    }

    public static function fromHistory(\Iterator $historyEvents): self
    {
        return self::reconstituteFromHistory($historyEvents);
    }

    public function id(): string
    {
        return $this->id;
    }

    public function changeName(string $newName): void
    {
        $this->recordThat(UserNameChanged::occur($this->id, ['username' => $newName]));
    }

    public function name(): string
    {
        return $this->name;
    }

    protected function whenUserCreated(UserCreated $event): void
    {
        $this->id = $event->userId();
        $this->name = $event->name();
    }

    protected function whenUsernameChanged(UserNameChanged $event): void
    {
        $this->name = $event->newUsername();
    }

    /**
     * @return \Prooph\EventSourcing\AggregateChanged[]
     */
    public function accessRecordedEvents(): array
    {
        return $this->popRecordedEvents();
    }

    /**
     * @return string representation of the unique identifier of the aggregate root
     */
    protected function aggregateId(): string
    {
        return $this->id();
    }
}
