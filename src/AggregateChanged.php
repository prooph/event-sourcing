<?php
/*
 * This file is part of the prooph/event-sourcing package.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 06/06/14 - 22:14
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
     * @var array
     */
    protected $payload = [];

    /**
     * @param string $aggregateId
     * @param array $payload
     * @return static
     */
    public static function occur($aggregateId, array $payload = [])
    {
        return new static($aggregateId, $payload);
    }

    /**
     * @param string $aggregateId
     * @param array $payload
     * @param array $metadata
     */
    protected function __construct($aggregateId, array $payload, array $metadata = [])
    {
        //Metadata needs to be set before setAggregateId is called
        $this->metadata = $metadata;
        $this->setAggregateId($aggregateId);
        $this->setPayload($payload);
        $this->init();
    }

    /**
     * @return string
     */
    public function aggregateId()
    {
        return $this->metadata['aggregate_id'];
    }

    /**
     * Return message payload as array
     *
     * The payload should only contain scalar types and sub arrays.
     * The payload is normally passed to json_encode to persist the message or
     * push it into a message queue.
     *
     * @return array
     */
    public function payload()
    {
        return $this->payload;
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

    /**
     * This method is called when message is instantiated named constructor fromArray
     *
     * @param array $payload
     * @return void
     */
    protected function setPayload(array $payload)
    {
        $this->payload = $payload;
    }
}
