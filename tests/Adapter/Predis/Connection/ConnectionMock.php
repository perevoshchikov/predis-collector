<?php

namespace Anper\RedisCollector\Tests\Adapter\Predis\Connection;

use Predis\Command\CommandInterface;
use Predis\Connection\ConnectionInterface;

class ConnectionMock implements ConnectionInterface
{
    /**
     * @inheritDoc
     */
    public function connect()
    {
        //
    }

    /**
     * @inheritDoc
     */
    public function disconnect()
    {
        //
    }

    /**
     * @inheritDoc
     */
    public function isConnected()
    {
        //
    }

    /**
     * @inheritDoc
     */
    public function writeRequest(CommandInterface $command)
    {
        //
    }

    /**
     * @inheritDoc
     */
    public function readResponse(CommandInterface $command)
    {
        //
    }

    /**
     * @inheritDoc
     */
    public function executeCommand(CommandInterface $command)
    {
        //
    }
    /**
     * @param mixed $name
     * @return mixed
     */
    public function __get($name)
    {
       return $name;
    }

    /**
     * @param mixed $name
     * @param mixed $value
     */
    public function __set($name, $value)
    {
        //
    }

    /**
     * @param mixed $name
     * @param array $args
     * @return mixed
     */
    public function __call($name, $args)
    {
        return $name;
    }

    public function __toString()
    {
        return '';
    }
}
