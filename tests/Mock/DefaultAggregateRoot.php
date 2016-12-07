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

use Prooph\Common\Messaging\Message;

final class DefaultAggregateRoot implements DefaultAggregateRootContract
{
    private $historyEvents = [];

    /**
     * @var int
     */
    private $version = 0;

    public function getVersion(): int
    {
        return $this->version;
    }

    public static function reconstituteFromHistory(\Iterator $historyEvents): DefaultAggregateRootContract
    {
        $self = new self();

        $self->historyEvents = iterator_to_array($historyEvents);

        return $self;
    }

    public function getHistoryEvents(): array
    {
        return $this->historyEvents;
    }

    public function getId(): string
    {
        // not required for this mock
    }

    /**
     * @return Message[]
     */
    public function popRecordedEvents(): array
    {
        // not required for this mock
    }

    public function replay($event): void
    {
        // not required for this mock
    }
}
