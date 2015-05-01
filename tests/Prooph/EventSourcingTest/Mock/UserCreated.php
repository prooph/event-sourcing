<?php
/*
 * This file is part of the prooph/event-store.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 18.04.14 - 00:29
 */

namespace Prooph\EventSourcingTest\Mock;

use Prooph\EventSourcing\AggregateChanged;

/**
 * Class UserCreated
 *
 * @package Prooph\EventStoreTest\Mock
 * @author Alexander Miertsch <contact@prooph.de>
 */
class UserCreated extends AggregateChanged
{
    /**
     * @return string
     */
    public function userId()
    {
        return $this->payload['id'];
    }

    /**
     * @return string
     */
    public function name()
    {
        return $this->payload['name'];
    }
}
 