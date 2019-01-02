<?php

namespace Anper\RedisCollector\Adapter\Predis\Connection;

use Anper\RedisCollector\Adapter\Predis\PredisAdapter;
use Predis\Command\CommandInterface;
use Predis\Connection\AggregateConnectionInterface;
use Predis\Connection\NodeConnectionInterface;

/**
 * Class AggregateConnection
 * @package Anper\RedisCollector\Adapter\Predis
 *
 * @property AggregateConnectionInterface $connection
 */
class AggregateConnection extends Connection implements AggregateConnectionInterface
{
    /**
     * @var PredisAdapter
     */
    protected $adapter;

    /**
     * @param AggregateConnectionInterface $connection
     * @param PredisAdapter $adapter
     */
    public function __construct(AggregateConnectionInterface $connection, PredisAdapter $adapter)
    {
        parent::__construct($connection);

        $this->adapter = $adapter;
    }

    /**
     * @inheritDoc
     */
    public function add(NodeConnectionInterface $connection)
    {
        $this->connection->add($connection);
    }

    /**
     * @inheritDoc
     */
    public function remove(NodeConnectionInterface $connection)
    {
        return $this->connection->remove($connection);
    }

    /**
     * @inheritDoc
     */
    public function getConnection(CommandInterface $command)
    {
        $connection = $this->connection->getConnection($command);

        return $this->wrapConnection($connection);
    }

    /**
     * @inheritDoc
     */
    public function getConnectionById($connectionID)
    {
        $connection = $this->connection->getConnectionById($connectionID);

        return $this->wrapConnection($connection);
    }

    /**
     * @param NodeConnectionInterface|null $connection
     * @return \Anper\RedisCollector\ConnectionInterface|null
     */
    protected function wrapConnection(?NodeConnectionInterface $connection)
    {
        if ($connection === null) {
            return null;
        }

        return $this->adapter->addConnection($connection);
    }
}
