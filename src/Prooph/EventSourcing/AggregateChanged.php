<?php
/*
 * This file is part of the prooph/event-sourcing package.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Prooph\EventSourcing;

use Assert\Assertion;
use Prooph\Common\Messaging\DomainEvent;

/**
 * AggregateChanged
 * 
 * @author Alexander Miertsch <contact@prooph.de>
 * @package Prooph\EventSourcing
 */
class AggregateChanged extends DomainEvent
{
    /**
     * @var string
     */
    protected $aggregateId;

    /**
     * @param string $aggregateId
     * @param array $payload
     * @return static
     */
    public static function occur($aggregateId, array $payload)
    {
        $instance = new static(get_called_class(), $payload, 1, null, null, ['aggregate_id' => $aggregateId]);

        //We reset version here, because the AggregateTranslator will inject the version of the aggregate via method trackVersion
        $instance->version = null;

        return $instance;
    }

    /**
     * @return string
     */
    public function aggregateId()
    {
        return $this->metadata['aggregate_id'];
    }

    /**
     * Track version of related aggregate
     *
     * @param int $version
     * @throws \BadMethodCallException If event already tracks a version
     */
    public function trackVersion($version)
    {
        if (! is_null($this->version)) {
            throw new \BadMethodCallException(sprintf(
                "DomainEvent %s (%s) already tracks a version",
                get_class($this),
                $this->uuid->toString()
            ));
        }

        $this->setVersion($version);
    }

    /**
     * @param int $version
     */
    protected function setVersion($version)
    {
        Assertion::integer($version);

        $this->version = $version;
    }

    /**
     * @param string $aggregateId
     */
    protected function setAggregateId($aggregateId)
    {
        Assertion::string($aggregateId);
        Assertion::notEmpty($aggregateId);

        $this->metadata['aggregate_id'] = $aggregateId;
    }
}
