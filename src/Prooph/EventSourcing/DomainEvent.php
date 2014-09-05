<?php
/*
 * This file is part of the prooph/event-sourcing.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 05.09.14 - 22:43
 */

namespace Prooph\EventSourcing;

use Rhumsaa\Uuid\Uuid;

interface DomainEvent
{
    /**
     * @return Uuid
     */
    public function uuid();

    /**
     * @return \DateTime
     */
    public function occurredOn();

    /**
     * @return array
     */
    public function payload();
}
 