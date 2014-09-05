<?php
/*
 * This file is part of the prooph/event-sourcing package.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Prooph\EventSourcing\DomainEvent;

use Codeliner\ArrayReader\ArrayReader;
use Rhumsaa\Uuid\Uuid;

/**
 * AggregateChangedEvent
 * 
 * @author Alexander Miertsch <contact@prooph.de>
 * @package Prooph\EventSourcing
 */
class AggregateChangedEvent implements DomainEvent
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
     * @param mixed $aggregateId
     * @param array $payload
     * @param Uuid $uuid
     * @param \DateTime $occurredOn
     */
    public function __construct($aggregateId, array $payload, Uuid $uuid = null, \DateTime $occurredOn = null)
    {
        if (is_null($uuid)) {
            $uuid = Uuid::uuid4();
        }

        if (is_null($occurredOn)) {
            $occurredOn = new \DateTime();
        }

        $this->aggregateId = $aggregateId;
        $this->payload     = $payload;
        $this->uuid        = $uuid;
        $this->occurredOn  = $occurredOn;
    }

    /**
     * @return mixed
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
        return new ArrayReader($this->payload());
    }
}
