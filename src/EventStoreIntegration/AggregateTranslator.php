<?php
/*
 * This file is part of the prooph/event-sourcing.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 09/05/14 - 23:36
 */

namespace Prooph\EventSourcing\EventStoreIntegration;

use Iterator;
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
     * @param \Iterator $historyEvents
     * @return object reconstructed AggregateRoot
     */
    public function reconstituteAggregateFromHistory(AggregateType $aggregateType, \Iterator $historyEvents)
    {
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
     * @param object $anEventSourcedAggregateRoot
     * @param Iterator $events
     */
    public function applyPendingStreamEvents($anEventSourcedAggregateRoot, Iterator $events)
    {
        $this->getAggregateRootDecorator()->applyPendingStreamEvents($anEventSourcedAggregateRoot, $events);
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
