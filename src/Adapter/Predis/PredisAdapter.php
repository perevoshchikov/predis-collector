<?php

namespace Anper\RedisCollector\Adapter\Predis;

use Anper\RedisCollector\Adapter\Predis\Connection\AggregateConnection;
use Anper\RedisCollector\Adapter\Predis\Connection\Connection;
use Anper\RedisCollector\Adapter\Predis\Connection\NodeConnection;
use Anper\RedisCollector\Adapter\Predis\Exception\InvalidConnectionException;
use Anper\RedisCollector\ConnectionInterface;
use Anper\RedisCollector\Format\FormatterInterface;
use Anper\RedisCollector\RedisCollector;
use Predis\Client;
use Predis\Connection\AggregateConnectionInterface;
use Predis\Connection\ConnectionInterface as PredisConnection;
use Predis\Connection\NodeConnectionInterface;

/**
 * Class PredisAdapter
 * @package Anper\RedisCollector\Adapter\Predis
 */
class PredisAdapter
{
    /**
     * @var RedisCollector
     */
    protected $collector;

    /**
     * @param RedisCollector $collector
     */
    public function __construct(RedisCollector $collector)
    {
        $this->collector = $collector;
    }

    /**
     * @param Client $client
     * @return ConnectionInterface
     */
    public function addClient(Client $client): ConnectionInterface
    {
        return $this->replaceConnection($client);
    }

    /**
     * @param PredisConnection $connection
     * @return ConnectionInterface
     */
    public function addConnection(PredisConnection $connection): ConnectionInterface
    {
        $wrapped = $this->wrapConnection($connection);

        $this->collector->addConnection($wrapped);

        if ($wrapped instanceof FormatterInterface) {
            $this->collector->addResponseFormatter($wrapped);
        }

        return $wrapped;
    }

    /**
     * @param PredisConnection $connection
     * @return ConnectionInterface
     */
    public function wrapConnection(PredisConnection $connection): ConnectionInterface
    {
        if ($connection instanceof AggregateConnectionInterface) {
            return new AggregateConnection($connection, $this);
        }

        if ($connection instanceof NodeConnectionInterface) {
            return new NodeConnection($connection);
        }

        return new Connection($connection);
    }

    /**
     * @param Client $client
     * @return ConnectionInterface
     */
    protected function replaceConnection(Client $client): ConnectionInterface
    {
        $connection = $this->getConnection($client);

        $wrapped = $this->addConnection($connection);

        $this->setConnection($client, $wrapped);

        return $wrapped;
    }

    /**
     * @param Client $client
     * @return PredisConnection
     */
    protected function getConnection(Client $client): PredisConnection
    {
        $getter = \Closure::bind(function ($client) {
            return $client->connection;
        }, null, $client);

        $connection = $getter($client);

        if ($connection instanceof PredisConnection) {
            return $connection;
        }

        throw new InvalidConnectionException(sprintf(
            'Expected client connection instance of "%s", given "%s"',
            PredisConnection::class,
            \is_object($connection) ? \get_class($connection) : \gettype($connection)
        ));
    }

    /**
     * @param Client $client
     * @param ConnectionInterface $connection
     */
    protected function setConnection(Client $client, ConnectionInterface $connection): void
    {
        $setter = \Closure::bind(function ($client, $connection) {
            $client->connection = $connection;
        }, null, $client);

        $setter($client, $connection);
    }
}
