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

use Prooph\EventSourcing\EventStoreIntegration\AggregateRootDecorator;

/**
 * Class ExtendedAggregateRootDecorator
 * @package ProophTest\EventSourcing\Mock
 */
class ExtendedAggregateRootDecorator extends AggregateRootDecorator
{


    /**
     * @return string
     */
    public function getAggregateId()
    {
        return $this->aggregateId();
    }
}
