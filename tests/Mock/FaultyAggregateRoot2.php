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

final class FaultyAggregateRoot2
{
    public function getVersion(): int
    {
        //faulty return
        return 1;
    }

    public static function reconstituteFromHistory(\Iterator $historyEvents): DefaultAggregateRootContract
    {
        //faulty method
        return new class() implements DefaultAggregateRootContract {
            public static function reconstituteFromHistory(\Iterator $historyEvents): DefaultAggregateRootContract
            {
                return new self();
            }

            public function getVersion(): int
            {
                return 1;
            }

            public function getId(): string
            {
                return 'id';
            }

            public function popRecordedEvents(): void
            {
            }

            public function replay($event): void
            {
            }
        };
    }

    public function getId(): string
    {
        //faulty method
        return '0';
    }

    public function popRecordedEvents(): void
    {
    }

    public function replay($event): void
    {
    }
}
