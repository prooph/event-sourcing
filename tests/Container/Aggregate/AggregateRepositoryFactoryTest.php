<?php
/**
 * This file is part of the prooph/event-sourcing.
 * (c) 2014-2017 prooph software GmbH <contact@prooph.de>
 * (c) 2015-2017 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
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
}
