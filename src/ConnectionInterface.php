<?php

namespace Anper\RedisCollector;

/**
 * Interface ConnectionInterface
 * @package Anper\RedisCollector
 */
interface ConnectionInterface
{
    /**
     * @return Statement[]
     */
    public function getExecutedStatements(): array;

    /**
     * @return string
     */
    public function getConnectionId(): string;
}
