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

namespace ProophTest\EventSourcing\Helper;

use Prooph\EventStoreClient\ConnectionSettingsBuilder;
use Prooph\EventStoreClient\EventStoreAsyncConnection;
use Prooph\EventStoreClient\EventStoreConnectionBuilder;
use Prooph\EventStoreClient\EventStoreSyncConnection;
use Prooph\EventStoreClient\Internal\EventStoreSyncNodeConnection;
use Prooph\EventStoreClient\IpEndPoint;
use Prooph\EventStoreClient\UserCredentials;

/** @internal */
class Connection
{
    public static function createAsync(): EventStoreAsyncConnection
    {
        self::checkRequiredEnvironmentSettings();

        $host = \getenv('ES_HOST');
        $port = (int) \getenv('ES_PORT');
        $user = \getenv('ES_USER');
        $pass = \getenv('ES_PASS');

        $settingsBuilder = new ConnectionSettingsBuilder();
        $settingsBuilder->setDefaultUserCredentials(new UserCredentials($user, $pass));

        return EventStoreConnectionBuilder::createAsyncFromIpEndPoint(
            new IpEndPoint($host, $port),
            $settingsBuilder->build()
        );
    }

    public static function createSync(): EventStoreSyncConnection
    {
        return new EventStoreSyncNodeConnection(self::createAsync());
    }

    private static function checkRequiredEnvironmentSettings(): void
    {
        $env = \getenv();

        if (! isset(
            $env['ES_HOST'],
            $env['ES_PORT'],
            $env['ES_USER'],
            $env['ES_PASS']
        )) {
            throw new \RuntimeException('Environment settings for event store connection not found');
        }
    }
}
