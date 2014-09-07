<?php
/*
 * This file is part of the prooph/event-store.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 17.04.14 - 21:45
 */

namespace Prooph\EventSourcingTest;

use Prooph\EventSourcing\AggregateChanged;

/**
 * Class AggregateChangedTest
 *
 * @package Prooph\EventSourcingTest\EventSourcing
 * @author Alexander Miertsch <contact@prooph.de>
 */
class AggregateChangedTest extends TestCase
{
    /**
     * @test
     */
    public function it_has_a_new_uuid_after_construct()
    {
        $event = AggregateChanged::occur('1', array());

        $this->assertInstanceOf('Rhumsaa\Uuid\Uuid', $event->uuid());
    }

    /**
     * @test
     */
    public function it_references_an_aggregate()
    {
        $event = AggregateChanged::occur('1', array());

        $this->assertEquals(1, $event->aggregateId());
    }

    /**
     * @test
     */
    public function it_has_an_occurred_on_datetime_after_construct()
    {
        $event = AggregateChanged::occur('1', array());

        $this->assertInstanceOf('\DateTime', $event->occurredOn());
    }

    /**
     * @test
     */
    public function it_has_assigned_payload_after_construct()
    {
        $payload = array('test payload');

        $event = AggregateChanged::occur('1', $payload);

        $this->assertEquals($payload, $event->payload());
    }

    /**
     * @test
     */
    public function it_returns_an_array_reader_with_the_populated_payload()
    {
        $event = AggregateChanged::occur('1', array('key' => 'value'));

        $this->assertEquals('value', $event->toPayloadReader()->stringValue('key'));
    }

    /**
     * @test
     */
    public function it_can_track_aggregate_version()
    {
        $event = AggregateChanged::occur('1', array('key' => 'value'));

        $event->trackVersion(2);

        $this->assertEquals(2, $event->version());
    }

    /**
     * @test
     */
    public function it_only_tracks_version_once()
    {
        $event = AggregateChanged::occur('1', array('key' => 'value'));

        $event->trackVersion(2);

        $this->setExpectedException('\BadMethodCallException');

        $event->trackVersion(3);
    }
}
 