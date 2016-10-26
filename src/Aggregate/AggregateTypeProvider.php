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

namespace Prooph\EventSourcing\Aggregate;

/**
 * Interface AggregateTypeProvider
 *
 * @package Prooph\EventSourcing\Aggregate
 * @author Alexander Miertsch <contact@prooph.de>
 */
interface AggregateTypeProvider
{
    public function aggregateType(): AggregateType;
}
