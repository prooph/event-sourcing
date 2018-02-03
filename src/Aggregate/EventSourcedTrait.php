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

namespace Prooph\EventSourcing\Aggregate;

use Iterator;
use Prooph\EventSourcing\AggregateChanged;
use RuntimeException;

trait EventSourcedTrait
{
    /**
     * Current version
     *
     * @var int
     */
    protected $version = 0;

    /**
     * @throws RuntimeException
     */
    protected static function reconstituteFromHistory(Iterator $historyEvents): self
    {
        $instance = new static();
        $instance->replay($historyEvents);

        return $instance;
    }

    /**
     * Replay past events
     *
     * @throws RuntimeException
     */
    protected function replay(Iterator $historyEvents): void
    {
        foreach ($historyEvents as $pastEvent) {
            /** @var AggregateChanged $pastEvent */
            $this->version = $pastEvent->version();

            $this->apply($pastEvent);
        }
    }

    abstract protected function aggregateId(): string;

    /**
     * Apply given event
     */
    abstract protected function apply(AggregateChanged $event): void;
}
