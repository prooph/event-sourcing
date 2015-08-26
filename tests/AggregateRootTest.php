<?php
/*
 * This file is part of the prooph/event-store.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 18.04.14 - 00:03
 */

namespace Prooph\EventSourcingTest;

use Prooph\EventSourcingTest\Mock\BrokenUser;
use Prooph\EventSourcingTest\Mock\User;

/**
 * Class AggregateRootTest
 *
 * @package Prooph\EventSourcingTest\EventSourcing
 * @author Alexander Miertsch <contact@prooph.de>
 */
class AggregateRootTest extends TestCase
{
    /**
     * @test
     */
    public function it_applies_event_by_calling_appropriate_event_handler()
    {
        $user = User::nameNew('John');

        $this->assertEquals('John', $user->name());

        $user->changeName('Max');

        $this->assertEquals('Max', $user->name());

        $pendingEvents = $user->accessRecordedEvents();

        $this->assertEquals(2, count($pendingEvents));

        $userCreatedEvent = $pendingEvents[0];

        $this->assertEquals('John', $userCreatedEvent->name());
        $this->assertEquals(1, $userCreatedEvent->version());

        $userNameChangedEvent = $pendingEvents[1];

        $this->assertEquals('Max', $userNameChangedEvent->newUsername());
        $this->assertEquals(2, $userNameChangedEvent->version());
    }

    /**
     * @test
     * @expectedException RuntimeException
     * @expectedExceptionMessage Missing event handler method whenUserCreated for aggregate root Prooph\EventSourcingTest\Mock\BrokenUser
     */
    public function it_throws_exception_when_no_handler_on_aggregate()
    {
        BrokenUser::nameNew('John');
    }

    /**
     * @test
     */
    public function it_reconstructs_itself_from_history()
    {
        $user = User::nameNew('John');

        $this->assertEquals('John', $user->name());

        $user->changeName('Max');

        $historyEvents = $user->accessRecordedEvents();

        $sameUser = User::fromHistory($historyEvents);

        $this->assertEquals($user->id(), $sameUser->id());
        $this->assertEquals($user->name(), $sameUser->name());
    }

    /**
     * @test
     */
    public function it_clears_pending_events_after_returning_them()
    {
        $user = User::nameNew('John');

        $recordedEvens = $user->accessRecordedEvents();

        $this->assertEquals(1, count($recordedEvens));

        $recordedEvens = $user->accessRecordedEvents();

        $this->assertEquals(0, count($recordedEvens));
    }
}
