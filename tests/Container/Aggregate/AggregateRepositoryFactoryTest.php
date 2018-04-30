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

namespace ProophTest\EventSourcing\Container\Aggregate;

use InvalidArgumentException;
use Prooph\EventSourcing\Aggregate\AggregateTranslator;
use Prooph\EventSourcing\Container\Aggregate\AggregateRepositoryFactory;
use Prooph\EventStore\EventStore;
use Prooph\EventStore\Exception\ConfigurationException;
use ProophTest\EventSourcing\Mock\EventStoreMock;
use ProophTest\EventSourcing\Mock\RepositoryMock;
use ProophTest\EventStore\ActionEventEmitterEventStoreTestCase;
use ProophTest\EventStore\Mock\User;
use Psr\Container\ContainerInterface;

class AggregateRepositoryFactoryTest extends ActionEventEmitterEventStoreTestCase
{
    /**
     * @test
     */
    public function it_creates_an_aggregate_from_static_call(): void
    {
        $container = $this->prophesize(ContainerInterface::class);
        $container->has('config')->willReturn(true);
        $container->get('config')->willReturn([
            'prooph' => [
                'event_sourcing' => [
                    'aggregate_repository' => [
                        'repository_mock' => [
                            'repository_class' => RepositoryMock::class,
                            'aggregate_type' => User::class,
                            'aggregate_translator' => 'user_translator',
                        ],
                    ],
                ],
            ],
        ]);
        $container->get(EventStore::class)->willReturn($this->eventStore);

        $userTranslator = $this->prophesize(AggregateTranslator::class);

        $container->get('user_translator')->willReturn($userTranslator->reveal());

        $factory = [AggregateRepositoryFactory::class, 'repository_mock'];
        self::assertInstanceOf(RepositoryMock::class, $factory($container->reveal()));
    }

    /**
     * @test
     */
    public function it_creates_an_aggregate_from_static_call_with_custom_event_store(): void
    {
        $container = $this->prophesize(ContainerInterface::class);
        $container->has('config')->willReturn(true);
        $container->get('config')->willReturn([
            'prooph' => [
                'event_sourcing' => [
                    'aggregate_repository' => [
                        'repository_mock' => [
                            'repository_class' => RepositoryMock::class,
                            'event_store' => EventStoreMock::class,
                            'aggregate_type' => User::class,
                            'aggregate_translator' => 'user_translator',
                        ],
                    ],
                ],
            ],
        ]);
        $container->get(EventStoreMock::class)->willReturn($this->eventStore);

        $userTranslator = $this->prophesize(AggregateTranslator::class);

        $container->get('user_translator')->willReturn($userTranslator->reveal());

        $factory = [AggregateRepositoryFactory::class, 'repository_mock'];
        self::assertInstanceOf(RepositoryMock::class, $factory($container->reveal()));
    }

    /**
     * @test
     */
    public function it_throws_invalid_argument_exception_without_container_on_static_call(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The first argument must be of type Psr\Container\ContainerInterface');

        AggregateRepositoryFactory::other_config_id();
    }

    /**
     * @test
     */
    public function it_throws_exception_when_unknown_repository_class_given(): void
    {
        $this->expectException(ConfigurationException::class);

        $container = $this->prophesize(ContainerInterface::class);
        $container->has('config')->willReturn(true);
        $container->get('config')->willReturn([
            'prooph' => [
                'event_sourcing' => [
                    'aggregate_repository' => [
                        'repository_mock' => [
                            'repository_class' => 'invalid',
                            'aggregate_type' => User::class,
                            'aggregate_translator' => 'user_translator',
                        ],
                    ],
                ],
            ],
        ]);

        $factory = new AggregateRepositoryFactory('repository_mock');
        $factory->__invoke($container->reveal());
    }

    /**
     * @test
     */
    public function it_throws_exception_when_invalid_repository_class_given(): void
    {
        $this->expectException(ConfigurationException::class);

        $container = $this->prophesize(ContainerInterface::class);
        $container->has('config')->willReturn(true);
        $container->get('config')->willReturn([
            'prooph' => [
                'event_sourcing' => [
                    'aggregate_repository' => [
                        'repository_mock' => [
                            'repository_class' => 'stdClass',
                            'aggregate_type' => User::class,
                            'aggregate_translator' => 'user_translator',
                        ],
                    ],
                ],
            ],
        ]);

        $factory = new AggregateRepositoryFactory('repository_mock');
        $factory->__invoke($container->reveal());
    }

    /**
     * @test
     */
    public function it_throws_exception_when_invalid_event_store_class_given(): void
    {
        $this->expectException(\TypeError::class);

        $container = $this->prophesize(ContainerInterface::class);
        $container->has('config')->willReturn(true);
        $container->get('config')->willReturn([
            'prooph' => [
                'event_sourcing' => [
                    'aggregate_repository' => [
                        'repository_mock' => [
                            'repository_class' => RepositoryMock::class,
                            'event_store' => 'stdClass',
                            'aggregate_type' => User::class,
                            'aggregate_translator' => 'user_translator',
                        ],
                    ],
                ],
            ],
        ]);
        $container->get('stdClass')->willReturn('stdClass');

        $userTranslator = $this->prophesize(AggregateTranslator::class);

        $container->get('user_translator')->willReturn($userTranslator->reveal());

        $factory = [AggregateRepositoryFactory::class, 'repository_mock'];
        self::assertInstanceOf(RepositoryMock::class, $factory($container->reveal()));
    }

    /**
     * @test
     */
    public function it_uses_given_aggregate_type_mapping(): void
    {
        $container = $this->prophesize(ContainerInterface::class);
        $container->has('config')->willReturn(true);
        $container->get('config')->willReturn([
            'prooph' => [
                'event_sourcing' => [
                    'aggregate_repository' => [
                        'repository_mock' => [
                            'repository_class' => RepositoryMock::class,
                            'aggregate_type' => [
                                'user' => User::class,
                            ],
                            'aggregate_translator' => 'user_translator',
                        ],
                    ],
                ],
            ],
        ]);
        $container->get(EventStore::class)->willReturn($this->eventStore);

        $userTranslator = $this->prophesize(AggregateTranslator::class);

        $container->get('user_translator')->willReturn($userTranslator->reveal());

        $factory = [AggregateRepositoryFactory::class, 'repository_mock'];
        self::assertInstanceOf(RepositoryMock::class, $factory($container->reveal()));
    }
}
