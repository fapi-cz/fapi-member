<?php

namespace FapiMember\Library\MongoDB\Driver\Monitoring;

use MongoDB\BSON\ObjectId;
/**
 * @since 1.13.0
 */
final class ServerOpeningEvent
{
    final private function __construct()
    {
    }
    /**
     * Returns the port on which this server is listening
     * @link https://www.php.net/manual/en/mongodb-driver-monitoring-serveropeningevent.getport.php
     */
    final public function getPort(): int
    {
    }
    /**
     * Returns the hostname of the server
     * @link https://www.php.net/manual/en/mongodb-driver-monitoring-serveropeningevent.gethost.php
     */
    final public function getHost(): string
    {
    }
    /**
     * Returns the topology ID associated with this server
     * @link https://www.php.net/manual/en/mongodb-driver-monitoring-serveropeningevent.gettopologyid.php
     */
    final public function getTopologyId(): ObjectId
    {
    }
    final public function __wakeup(): void
    {
    }
}
