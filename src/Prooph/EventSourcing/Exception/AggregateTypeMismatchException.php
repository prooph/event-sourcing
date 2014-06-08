<?php
/*
 * This file is part of the prooph/event-sourcing.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 08.06.14 - 12:52
 */

namespace Prooph\EventSourcing\Exception;

/**
 * Class AggregateTypeMismatchException
 *
 * @package Prooph\EventSourcing\Exception
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class AggregateTypeMismatchException extends \InvalidArgumentException implements EventSourcingException
{
}
 