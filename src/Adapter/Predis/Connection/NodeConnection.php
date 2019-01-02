<?php

namespace Anper\RedisCollector\Adapter\Predis\Connection;

use Predis\Command\CommandInterface;
use Predis\Connection\NodeConnectionInterface;

/**
 * Class NodeConnection
 * @package Anper\RedisCollector\Adapter\Predis
 *
 * @property NodeConnectionInterface $connection
 */
class NodeConnection extends Connection implements NodeConnectionInterface
{
    /**
     * @param NodeConnectionInterface $connection
     */
    public function __construct(NodeConnectionInterface $connection)
    {
        parent::__construct($connection);
    }

    /**
     * @inheritDoc
     */
    public function __toString()
    {
        return $this->connection->__toString();
    }

    /**
     * @inheritDoc
     */
    public function getResource()
    {
        return $this->connection->getResource();
    }

    /**
     * @inheritDoc
     */
    public function getParameters()
    {
        return $this->connection->getParameters();
    }

    /**
     * @inheritDoc
     */
    public function addConnectCommand(CommandInterface $command)
    {
        $this->connection->addConnectCommand($command);
    }

    /**
     * @inheritDoc
     */
    public function read()
    {
        return $this->connection->read();
    }
}
