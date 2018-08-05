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
use PHPUnit\Framework\TestCase;
use Prooph\EventSourcing\Aggregate\AggregateTranslator;
use Prooph\EventSourcing\Container\Aggregate\AggregateRepositoryFactory;
use Prooph\EventStoreClient\EventStoreSyncConnection;
use ProophTest\EventSourcing\Helper\Connection;
use ProophTest\EventSourcing\Mock\RepositoryMock;
use ProophTest\EventSourcing\Mock\User;
use ProophTest\EventSourcing\Mock\UserCreated;
use Psr\Container\ContainerInterface;

class AggregateRepositoryFactoryTest extends TestCase
{
    /**
     * @var EventStoreSyncConnection
     */
    protected $eventStoreClient;

    protected function setUp(): void
    {
        $this->eventStoreClient = Connection::createSync();
    }

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
                            'event_store_connection' => 'client',
                            'repository_class' => RepositoryMock::class,
                            'aggregate_type' => [
                                'user' => User::class,
                            ],
                            'message_map' => [
                                'user_created' => UserCreated::class,
                            ],
                            'category' => 'user',
                            'aggregate_translator' => 'user_translator',
                        ],
                    ],
                ],
            ],
        ]);
        $container->get('client')->willReturn($this->eventStoreClient);

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
        $this->expectException(\RuntimeException::class);

        $container = $this->prophesize(ContainerInterface::class);
        $container->has('config')->willReturn(true);
        $container->get('config')->willReturn([
            'prooph' => [
                'event_sourcing' => [
                    'aggregate_repository' => [
                        'repository_mock' => [
                            'repository_class' => 'invalid',
                            'aggregate_type' => [
                                'user' => User::class,
                            ],
                            'message_map' => [
                                'user_created' => UserCreated::class,
                            ],
                            'category' => 'user',
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
        $this->expectException(\RuntimeException::class);

        $container = $this->prophesize(ContainerInterface::class);
        $container->has('config')->willReturn(true);
        $container->get('config')->willReturn([
            'prooph' => [
                'event_sourcing' => [
                    'aggregate_repository' => [
                        'repository_mock' => [
                            'repository_class' => 'stdClass',
                            'aggregate_type' => [
                                'user' => User::class,
                            ],
                            'message_map' => [
                                'user_created' => UserCreated::class,
                            ],
                            'category' => 'user',
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
                            'event_store_connection' => 'stdClass',
                            'aggregate_type' => [
                                'user' => User::class,
                            ],
                            'message_map' => [
                                'user_created' => UserCreated::class,
                            ],
                            'category' => 'user',
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
}
