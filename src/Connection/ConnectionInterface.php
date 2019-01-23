<?php

namespace Anper\PredisCollector\Connection;

use Anper\PredisCollector\Profile;

/**
 * Interface ConnectionInterface
 * @package Anper\PredisCollector\Connection
 */
interface ConnectionInterface
{
    /**
     * @return Profile[]
     */
    public function getProfiles(): array;

    /**
     * @return string
     */
    public function getConnectionId(): string;
}
