<?php
/**
 * This file is part of the prooph/event-sourcing.
 * (c) 2014-2018 prooph software GmbH <contact@prooph.de>
 * (c) 2015-2018 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace ProophTest\EventSourcing\Aggregate;

use PHPUnit\Framework\TestCase;
use Prooph\EventSourcing\Aggregate\AggregateType;
use ProophTest\EventSourcing\Mock\User;

class AggregateTypeTest extends TestCase
{
    /** @var AggregateType */
    private $aggregateType;

    protected function setUp(): void
    {
        $this->aggregateType = new AggregateType([
            'user' => User::class,
        ]);
    }

    /** @test */
    public function it_returns_class_name(): void
    {
        $this->assertSame(User::class, $this->aggregateType->className('user'));
    }

    /** @test */
    public function it_returns_type_from_class_name(): void
    {
        $this->assertSame('user', $this->aggregateType->typeFromClassName(User::class));
    }

    /** @test */
    public function it_returns_type_from_aggregate_root(): void
    {
        $this->assertSame('user', $this->aggregateType->typeFromAggregate(User::nameNew('Alex')));
    }
}
