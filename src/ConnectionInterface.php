<?php

namespace Anper\RedisCollector;

/**
 * Interface ConnectionInterface
 * @package Anper\RedisCollector
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
