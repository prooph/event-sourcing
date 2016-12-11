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

namespace ProophTest\EventSourcing\Mock\JsonSerializable;

use ProophTest\EventSourcing\Mock\UserNameChanged as UserNameChangedRoot;

class UserNameChanged extends UserNameChangedRoot implements \JsonSerializable
{
    public function jsonSerialize()
    {
        return [
            'uuid' => $this->uuid,
            'messageName' => $this->messageName,
            'createdAt' => $this->createdAt,
            'metadata' => $this->metadata,
            'payload' => $this->payload,
        ];
    }
}
