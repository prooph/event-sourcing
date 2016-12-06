<?php
/**
 * This file is part of the prooph/event-sourcing.
 * (c) 2014-2016 prooph software GmbH <contact@prooph.de>
 * (c) 2015-2016 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace ProophTest\EventSourcing;

use PHPUnit\Framework\TestCase;
use Prooph\EventSourcing\EventStoreIntegration\AggregateRootDecorator;
use ProophTest\EventSourcing\Mock\BrokenUser;
use ProophTest\EventSourcing\Mock\User;
use ProophTest\EventSourcing\Mock\UserCreated;

class AggregateRootTest extends TestCase
{
    /**
     * @test
     */
    public function it_applies_event_by_calling_appropriate_event_handler()
    {
        $decorator = AggregateRootDecorator::newInstance();

        $user = User::nameNew('John');

        $recordedEvents = $decorator->extractRecordedEvents($user);

        //Recording and applying events are two different steps
        //In between would be the process of persisting recorded events to an event stream
        //Only if this was successful the events can be applied to the aggregate root
        //We skip the persistence process here and apply the events directly
        $decorator->replayStreamEvents($user, new \ArrayIterator($recordedEvents));

        $this->assertEquals('John', $user->name());

        $user->changeName('Max');

        $additionalRecordedEvents = $decorator->extractRecordedEvents($user);

        $decorator->replayStreamEvents($user, new \ArrayIterator($additionalRecordedEvents));

        $this->assertEquals('Max', $user->name());

        $recordedEvents = array_merge($recordedEvents, $additionalRecordedEvents);

        $this->assertEquals(2, count($recordedEvents));

        $userCreatedEvent = $recordedEvents[0];

        $this->assertEquals('John', $userCreatedEvent->name());
        $this->assertEquals(1, $userCreatedEvent->version());

        $userNameChangedEvent = $recordedEvents[1];

        $this->assertEquals('Max', $userNameChangedEvent->newUsername());
        $this->assertEquals(2, $userNameChangedEvent->version());
    }

    /**
     * @test
     */
    public function it_throws_exception_when_no_handler_on_aggregate()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unknown event "' . UserCreated::class . '" applied to user aggregate');

        $brokenUser = BrokenUser::nameNew('John');

        AggregateRootDecorator::newInstance()->applyStreamEvents(
            $brokenUser,
            new \ArrayIterator($brokenUser->accessRecordedEvents())
        );
    }

    /**
     * @test
     */
    public function it_reconstructs_itself_from_history()
    {
        $user = User::nameNew('John');

        $recordedEvents = $user->accessRecordedEvents();

        AggregateRootDecorator::newInstance()->replayStreamEvents($user, new \ArrayIterator($recordedEvents));

        $user->changeName('Max');

        $additionalRecordedEvents = $user->accessRecordedEvents();

        AggregateRootDecorator::newInstance()->replayStreamEvents($user, new \ArrayIterator($additionalRecordedEvents));

        $historyEvents = new \ArrayIterator(array_merge($recordedEvents, $additionalRecordedEvents));

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
