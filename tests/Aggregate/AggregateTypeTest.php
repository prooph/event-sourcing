<?php

/**
 * This file is part of prooph/event-sourcing.
 * (c) 2014-2019 Alexander Miertsch <kontakt@codeliner.ws>
 * (c) 2015-2019 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace ProophTest\EventSourcing\Aggregate;

use PHPUnit\Framework\TestCase;
use Prooph\EventSourcing\Aggregate\AggregateType;
use Prooph\EventSourcing\Aggregate\AggregateTypeProvider;
use Prooph\EventSourcing\Aggregate\Exception\AggregateTypeException;
use Prooph\EventSourcing\Aggregate\Exception\InvalidArgumentException;
use ProophTest\EventStore\Mock\Post;
use ProophTest\EventStore\Mock\User;

class AggregateTypeTest extends TestCase
{
    /**
     * @test
     */
    public function it_throws_exception_when_trying_to_create_from_string_as_aggregate_root(): void
    {
        $this->expectException(InvalidArgumentException::class);

        AggregateType::fromAggregateRoot('invalid');
    }

    /**
     * @test
     */
    public function it_delegates_on_creating_from_aggregate_root_when_it_implements_aggregate_type_provider(): void
    {
        $aggregateRoot = $this->prophesize(AggregateTypeProvider::class);
        $aggregateRoot->aggregateType()->willReturn(AggregateType::fromString('stdClass'))->shouldBeCalled();

        $this->assertEquals('stdClass', AggregateType::fromAggregateRoot($aggregateRoot->reveal())->toString());
    }

    /**
     * @test
     */
    public function it_creates_aggregate_type_from_mapping(): void
    {
        $aggregateType = AggregateType::fromMapping(['user' => User::class]);

        $this->assertSame('user', $aggregateType->toString());
        $this->assertSame(User::class, $aggregateType->mappedClass());
    }

    /**
     * @test
     */
    public function it_throws_exception_on_creating_from_aggregate_root_class_when_unknown_class_given(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Aggregate root class unknown_class can not be found');

        AggregateType::fromAggregateRootClass('unknown_class');
    }

    /**
     * @test
     */
    public function it_asserts_correct_aggregate_type(): void
    {
        $aggregateType = AggregateType::fromAggregateRootClass(User::class);

        $aggregateRoot = $this->prophesize(AggregateTypeProvider::class);

        $aggregateRoot->aggregateType()->willReturn(AggregateType::fromAggregateRootClass(User::class));

        $aggregateType->assert($aggregateRoot->reveal());

        $this->assertNull($aggregateType->mappedClass());
    }

    /**
     * @test
     */
    public function it_throws_exception_if_type_is_not_correct(): void
    {
        $this->expectException(AggregateTypeException::class);
        $this->expectExceptionMessage('Aggregate types must be equal. ProophTest\EventStore\Mock\User != ProophTest\EventStore\Mock\Post');

        $aggregateType = AggregateType::fromAggregateRootClass(User::class);

        $aggregateRoot = $this->prophesize(AggregateTypeProvider::class);

        $aggregateRoot->aggregateType()->willReturn(AggregateType::fromAggregateRootClass(Post::class));

        $aggregateType->assert($aggregateRoot->reveal());
    }

    /**
     * @test
     */
    public function it_delegates_to_string(): void
    {
        $type = AggregateType::fromAggregateRootClass('stdClass');
        $this->assertEquals('stdClass', (string) $type);
    }

    /**
     * @test
     */
    public function it_throws_exception_when_empty_aggregate_type_given(): void
    {
        $this->expectException(InvalidArgumentException::class);

        AggregateType::fromString('');
    }
}
