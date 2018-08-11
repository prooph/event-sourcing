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
    /** @var array */
    protected $map = [];

    // key = aggregate-type, value = aggregate-root-class
    public function __construct(array $map)
    {
        if (empty($map)) {
            throw new Exception\InvalidArgumentException('Map cannot be empty');
        }

        foreach ($map as $type => $class) {
            if (! \is_string($type) || empty($type)) {
                throw new Exception\InvalidArgumentException('Aggregate type must be a non-empty string');
            }

            if (! \is_string($class) || empty($class)) {
                throw new Exception\InvalidArgumentException('Aggregate root class must be a non-empty string');
            }

            $this->map[$type] = $class;
        }
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

    public function streamCategory(): string
    {
        return \key($this->map);
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
