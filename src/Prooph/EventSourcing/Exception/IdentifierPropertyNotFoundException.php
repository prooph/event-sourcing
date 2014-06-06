<?php
/*
 * This file is part of the prooph/event-store.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 17.04.14 - 23:35
 */

namespace Prooph\EventSourcing\Exception;

/**
 * Class IdentifierPropertyNotFoundException
 *
 * @package Prooph\EventSourcing\Exception
 * @author Alexander Miertsch <contact@prooph.de>
 */
class IdentifierPropertyNotFoundException extends \RuntimeException implements EventSourcingException
{
}
 