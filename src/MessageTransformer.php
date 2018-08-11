<?php
/**
 * This file is part of the prooph/event-sourcing.
 * (c) 2014-2018 prooph software GmbH <contact@prooph.de>
 * (c) 2015-2018 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Prooph\EventSourcing;

use Prooph\Common\Messaging\Message;
use Prooph\Common\Messaging\MessageFactory;
use Prooph\EventStoreClient\EventData;
use Prooph\EventStoreClient\EventId;
use Prooph\EventStoreClient\ResolvedEvent;

class MessageTransformer
{
    /** @var MessageFactory */
    protected $messageFactory;

    // key = event-type, value = aggregate-root-class
    public function __construct(MessageFactory $messageFactory)
    {
        $this->messageFactory = $messageFactory;
    }

    public function toMessage(ResolvedEvent $event): Message
    {
        $event = $event->originalEvent();

        $messageData = [
            'uuid' => $event->eventId()->toString(),
            'message_name' => $event->eventType(),
            'payload' => \json_decode($event->data(), true),
            'metadata' => \json_decode($event->metaData(), true),
            'created_at' => $event->created()->format('Y-m-d\TH:i:s.uP'),
        ];

        return $this->messageFactory->createMessageFromArray($messageData);
    }

    public function toEventData(Message $message): EventData
    {
        return new EventData(
            EventId::fromString($message->uuid()->toString()),
            $message->messageName(),
            true,
            \json_encode($message->payload()),
            \json_encode($message->metadata())
        );
    }
}
