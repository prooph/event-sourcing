<?php
/**
 * This file is part of the prooph/event-sourcing.
 *  (c) 2014-2016 prooph software GmbH <contact@prooph.de>
 *  (c) 2015-2016 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *  
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

declare(strict_types=1);

namespace ProophTest\EventSourcing\EventStoreIntegration;

use PHPUnit_Framework_TestCase as TestCase;
use Prooph\EventSourcing\EventStoreIntegration\AggregateRootDecorator;
use ProophTest\EventSourcing\Mock\ExtendedAggregateRootDecorator;

/**
 * Class AggregateRootDecoratorTest
 *
 * @package ProophTest\EventSourcing\EventStoreIntegration
 */
class AggregateRootDecoratorTest extends TestCase
{
    /**
     * @test
     */
    public function it_throws_exception_when_reconstitute_from_history_with_invalid_class()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Aggregate root class UnknownClass cannot be found');

        $decorator = AggregateRootDecorator::newInstance();
        $decorator->fromHistory('UnknownClass', new \ArrayIterator([]));
    }

    /**
     * @test
     */
    public function it_throws_exception_when_accessing_aggregate_id()
    {
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('The AggregateRootDecorator does not have an id');

        $decorator = ExtendedAggregateRootDecorator::newInstance();
        $decorator->getAggregateId();
    }
}
