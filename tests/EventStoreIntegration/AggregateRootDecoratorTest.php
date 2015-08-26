<?php
/*
 * This file is part of the prooph/event-sourcing.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 08/26/15 - 20:32
 */

namespace Prooph\EventSourcingTest\EventStoreIntegration;

use PHPUnit_Framework_TestCase as TestCase;
use Prooph\EventSourcing\EventStoreIntegration\AggregateRootDecorator;
use Prooph\EventSourcingTest\Mock\ExtendedAggregateRootDecorator;

/**
 * Class AggregateRootDecoratorTest
 *
 * @package Prooph\EventSourcingTest\EventStoreIntegration
 */
class AggregateRootDecoratorTest extends TestCase
{
    /**
     * @test
     * @expectedException RuntimeException
     * @expectedExceptionMessage Aggregate root class UnknownClass cannot be found
     */
    public function it_throws_exception_when_reconstitute_from_history_with_invalid_class()
    {
        $decorator = AggregateRootDecorator::newInstance();
        $decorator->fromHistory('UnknownClass', []);
    }

    /**
     * @test
     * @expectedException BadMethodCallException
     * @expectedExceptionMessage The AggregateRootDecorator does not have an id
     */
    public function it_throws_exception_when_accessing_aggregate_id()
    {
        $decorator = ExtendedAggregateRootDecorator::newInstance();
        $decorator->getAggregateId();
    }
}
