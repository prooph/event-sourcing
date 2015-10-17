<?php
/*
 * This file is part of the prooph/event-store.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 04/17/14 - 21:45
 */

namespace ProophTest\EventSourcing;

use Prooph\EventSourcing\AggregateChanged;

/**
 * Class AggregateChangedTest
 *
 * @package ProophTest\EventSourcing\EventSourcing
 * @author Alexander Miertsch <contact@prooph.de>
 */
class AggregateChangedTest extends TestCase
{
    /**
     * @test
     */
    public function it_has_a_new_uuid_after_construct()
    {
        $event = AggregateChanged::occur('1', []);

        $this->assertInstanceOf('Rhumsaa\Uuid\Uuid', $event->uuid());
    }

    /**
     * @test
     */
    public function it_references_an_aggregate()
    {
        $event = AggregateChanged::occur('1', []);

        $this->assertEquals(1, $event->aggregateId());
    }

    /**
     * @test
     */
    public function it_has_an_occurred_on_datetime_after_construct()
    {
        $event = AggregateChanged::occur('1', []);

        $this->assertInstanceOf('\DateTimeImmutable', $event->createdAt());
    }

    /**
     * @test
     */
    public function it_has_assigned_payload_after_construct()
    {
        $payload = ['test payload'];

        $event = AggregateChanged::occur('1', $payload);

        $this->assertEquals($payload, $event->payload());
    }

    /**
     * @test
     */
    public function it_can_track_aggregate_version_but_is_immutable()
    {
        $orgEvent = AggregateChanged::occur('1', ['key' => 'value']);

        $newEvent = $orgEvent->withVersion(2);

        $this->assertEquals(0, $orgEvent->version());
        $this->assertEquals(2, $newEvent->version());
    }
}
