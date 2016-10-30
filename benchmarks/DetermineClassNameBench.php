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

namespace ProophBench;

use Prooph\EventSourcing\AggregateChanged;

/**
 * @author Eric Braun <eb@oqq.be>
 *
 * @BeforeMethods({"initDomainEvent"})
 * @Revs(10000)
 * @Iterations(10)
 */
final class DetermineClassNameBench
{
    /** @var AggregateChanged */
    private $event;

    public function initDomainEvent(): void
    {
        $this->event = AggregateChanged::occur('1');
    }

    public function benchReflection(): void
    {
        (new \ReflectionClass($this->event))->getShortName();
    }

    public function benchArraySlice(): void
    {
        implode(array_slice(explode('\\', get_class($this->event)), -1));
    }

    public function benchArrayPop(): void
    {
        $path = explode('\\', get_class($this->event));
        array_pop($path);
    }

    public function benchSubstring(): void
    {
        $class = get_class($this->event);
        substr($class, strrpos($class, '\\') + 1);
    }

    public function benchEnd(): void
    {
        $path = explode('\\', get_class($this->event));
        end($path);
    }

    public function benchBasename(): void
    {
        basename(str_replace('\\', '/', get_class($this)));
    }
}
