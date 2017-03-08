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

use Iterator;
use Prooph\Common\Messaging\Message;

interface CustomAggregateRootContract
{
    public function version(): int;

    public static function buildFromHistoryEvents(Iterator $historyEvents): CustomAggregateRootContract;

    public function identifier(): string;

    /**
     * @return Message[]
     */
    public function getPendingEvents(): array;
}
