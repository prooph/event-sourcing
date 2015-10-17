<?php
/*
 * This file is part of the prooph/event-store.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 04/18/14 - 00:29
 */

namespace ProophTest\EventSourcing\Mock;

use Prooph\EventSourcing\AggregateChanged;

/**
 * Class UserCreated
 *
 * @package ProophTest\EventSourcing\Mock
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
