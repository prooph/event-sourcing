<?php
/**
 * This file is part of the prooph/event-sourcing.
 *  (c) 2014-2016 prooph software GmbH <contact@prooph.de>
 *  (c) 2015-2016 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *  
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
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
