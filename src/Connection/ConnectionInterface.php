<?php

namespace Anper\PredisCollector\Connection;

use Anper\PredisCollector\Profile;

/**
 * Interface ConnectionInterface
 * @package Anper\PredisCollector
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
