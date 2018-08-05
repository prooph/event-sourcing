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

namespace Prooph\EventSourcing\Aggregate;

class AggregateType
{
    /**
     * @var array
     */
    protected $map = [];

    // key = aggregate-type, value = aggregate-root-class
    public function __construct(array $map)
    {
        $this->map = $map;
    }

    public function className(string $type): string
    {
        if (! isset($this->map[$type])) {
            throw new Exception\InvalidArgumentException('Type ' . $type . ' not valid');
        }

        return $this->map[$type];
    }

    public function typeFromClassName(string $className): string
    {
        $map = \array_flip($this->map);

        if (! isset($map[$className])) {
            throw new Exception\InvalidArgumentException('Class name ' . $className . ' not valid');
        }

        return $map[$className];
    }

    public function typeFromAggregate(object $aggregateRoot): string
    {
        $className = \get_class($aggregateRoot);

        return $this->typeFromClassName($className);
    }

    /**
     * @throws Exception\AggregateTypeException
     */
    public function assert(object $aggregateRoot): void
    {
        $className = \get_class($aggregateRoot);

        $map = \array_flip($this->map);

        if (! isset($map[$className])) {
            throw new Exception\AggregateTypeException('Unknown aggregate root');
        }
    }
}
