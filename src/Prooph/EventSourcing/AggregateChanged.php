<?php
/*
 * This file is part of the prooph/event-sourcing package.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Prooph\EventSourcing;

use Codeliner\ArrayReader\ArrayReader;
use Rhumsaa\Uuid\Uuid;

/**
 * AggregateChanged
 * 
 * @author Alexander Miertsch <contact@prooph.de>
 * @package Prooph\EventSourcing
 */
class AggregateChanged implements DomainEvent
{
    /**
     * @var Uuid
     */
    protected $uuid;

    /**
     * @var mixed
     */
    protected $aggregateId;

    /**
     * This property is injected via Reflection
     *
     * @var int
     */
    protected $version;

    /**
     * @var array
     */
    protected $payload;

    /**
     * @var \DateTime
     */
    protected $occurredOn;

    /**
     * @var ArrayReader
     */
    private $payloadReader;

    /**
     * @param string $aggregateId
     * @param array $payload
     * @return static
     */
    public static function occur($aggregateId, array $payload)
    {
        return new static($aggregateId, $payload);
    }

    /**
     * @param string $aggregateId
     * @param array $payload
     * @param Uuid $uuid
     * @param \DateTime $occurredOn
     * @param $version
     * @return static
     */
    public static function reconstitute($aggregateId, array $payload, Uuid $uuid, \DateTime $occurredOn, $version)
    {
        return new static($aggregateId, $payload, $uuid, $occurredOn, $version);
    }

    /**
     * @param string $aggregateId
     * @param array $payload
     * @param Uuid $uuid
     * @param \DateTime $occurredOn
     * @param null|int $version
     */
    protected function __construct($aggregateId, array $payload, Uuid $uuid = null, \DateTime $occurredOn = null, $version = null)
    {
        if (is_null($uuid)) {
            $uuid = Uuid::uuid4();
        }

        if (is_null($occurredOn)) {
            $occurredOn = new \DateTime();
        }

        $this->setAggregateId($aggregateId);
        $this->payload     = $payload;
        $this->uuid        = $uuid;
        $this->occurredOn  = $occurredOn;

        if (! is_null($version)) {
            $this->setVersion($version);
        }
    }

    /**
     * @return string
     */
    public function aggregateId()
    {
        return $this->aggregateId;
    }

    /**
     * @return Uuid
     */
    public function uuid()
    {
        return $this->uuid;
    }

    /**
     * @return int
     */
    public function version()
    {
        return $this->version;
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
        \Assert\that($version)->integer();

        $this->version = $version;
    }

    /**
     * @return \DateTime
     */
    public function occurredOn()
    {
        return $this->occurredOn;
    }

    /**
     * @return array
     */
    public function payload()
    {
        return $this->payload;
    }

    /**
     * @return ArrayReader
     */
    public function toPayloadReader()
    {
        if (is_null($this->payloadReader)) {
            $this->payloadReader = new ArrayReader($this->payload());
        }

        return $this->payloadReader;
    }

    /**
     * @param string $aggregateId
     */
    protected function setAggregateId($aggregateId)
    {
        \Assert\that($aggregateId)->notEmpty()->string();

        $this->aggregateId = $aggregateId;
    }
}
