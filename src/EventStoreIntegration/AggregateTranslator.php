<?php
/*
 * This file is part of the prooph/event-sourcing.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 05.09.14 - 23:36
 */

namespace Prooph\EventSourcing\EventStoreIntegration;

use Prooph\Common\Messaging\Message;
use Prooph\EventStore\Aggregate\AggregateType;
use Prooph\EventStore\Aggregate\AggregateTranslator as EventStoreAggregateTranslator;

/**
 * Class AggregateTranslator
 *
 * @package Prooph\EventSourcing\EventStoreIntegration
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class AggregateTranslator implements EventStoreAggregateTranslator
{
    /**
     * @var AggregateRootDecorator
     */
    protected $aggregateRootDecorator;

    /**
     * @param object $anEventSourcedAggregateRoot
     * @return string
     */
    public function extractAggregateId($anEventSourcedAggregateRoot)
    {
        return (string)$this->getAggregateRootDecorator()->extractAggregateId($anEventSourcedAggregateRoot);
    }

    /**
     * @param AggregateType $aggregateType
     * @param Message[] $historyEvents
     * @throws \RuntimeException
     * @return object reconstructed AggregateRoot
     */
    public function reconstituteAggregateFromHistory(AggregateType $aggregateType, $historyEvents)
    {
        if (count($historyEvents) === 0) {
            throw new \RuntimeException(
                sprintf(
                    "Can not reconstitute Aggregate %s from history. No stream events given",
                    $aggregateType->toString()
                )
            );
        }

        return $this->getAggregateRootDecorator()
            ->fromHistory($aggregateType->toString(), $historyEvents);
    }

    /**
     * @param object $anEventSourcedAggregateRoot
     * @return Message[]
     */
    public function extractPendingStreamEvents($anEventSourcedAggregateRoot)
    {
        return $this->getAggregateRootDecorator()->extractRecordedEvents($anEventSourcedAggregateRoot);
    }

    /**
     * @return AggregateRootDecorator
     */
    public function getAggregateRootDecorator()
    {
        if (is_null($this->aggregateRootDecorator)) {
            $this->aggregateRootDecorator = AggregateRootDecorator::newInstance();
        }

        return $this->aggregateRootDecorator;
    }

    /**
     * @param AggregateRootDecorator $anAggregateRootDecorator
     */
    public function setAggregateRootDecorator(AggregateRootDecorator $anAggregateRootDecorator)
    {
        $this->aggregateRootDecorator = $anAggregateRootDecorator;
    }
}
