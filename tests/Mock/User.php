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

namespace ProophTest\EventSourcing\Mock;

use Prooph\EventSourcing\AggregateChanged;
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
        $instance = new static();

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

    protected function apply(AggregateChanged $e): void
    {
        switch (get_class($e)) {
            case UserCreated::class:
                $this->id = $e->userId();
                $this->name = $e->name();
                break;
            case UserNameChanged::class:
                $this->name = $e->newUsername();
                break;
            default:
                throw new \RuntimeException(
                    sprintf(
                        'Unknown event "%s" applied to user aggregate',
                        $e->messageName()
                    )
                );
        }
    }
}
