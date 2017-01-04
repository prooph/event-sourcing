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

namespace Prooph\EventSourcing\Util;

use IteratorIterator;
use Traversable;

final class MapIterator extends IteratorIterator
{
    /**
     * @var callable
     */
    private $callback;

    public function __construct(Traversable $iterator, callable $callback)
    {
        parent::__construct($iterator);
        $this->callback = $callback;
    }

    /**
     * @return mixed
     */
    public function current()
    {
        $callback = $this->callback;

        return $callback(parent::current());
    }
}
