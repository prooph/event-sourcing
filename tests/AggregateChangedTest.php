<?php
/**
 * This file is part of the prooph/event-sourcing.
 * (c) 2014-2017 prooph software GmbH <contact@prooph.de>
 * (c) 2015-2017 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace ProophTest\EventSourcing;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Prooph\EventSourcing\AggregateChanged;
use Ramsey\Uuid\Uuid;

class AggregateChangedTest extends TestCase
{
    /**
     * @test
     */
    public function it_has_a_new_uuid_after_construct(): void
    {
        $event = AggregateChanged::occur('1', []);

        $this->assertInstanceOf(Uuid::class, $event->uuid());
    }

    /**
     * @test
     */
    public function it_references_an_aggregate(): void
    {
        $event = AggregateChanged::occur('1', []);

        $this->assertEquals(1, $event->aggregateId());
    }

    /**
     * @test
     */
    public function it_has_an_occurred_on_datetime_after_construct(): void
    {
        $event = AggregateChanged::occur('1', []);

        $this->assertInstanceOf(DateTimeImmutable::class, $event->createdAt());
    }

    /**
     * @test
     */
    public function it_has_assigned_payload_after_construct(): void
    {
        $payload = ['test payload'];

        $event = AggregateChanged::occur('1', $payload);

        $this->assertEquals($payload, $event->payload());
    }

    /**
     * @test
     */
    public function it_can_track_aggregate_version_but_is_immutable(): void
    {
        $orgEvent = AggregateChanged::occur('1', ['key' => 'value']);

        $newEvent = $orgEvent->withVersion(2);

        $this->assertEquals(1, $orgEvent->version());
        $this->assertEquals(2, $newEvent->version());
    }
}
