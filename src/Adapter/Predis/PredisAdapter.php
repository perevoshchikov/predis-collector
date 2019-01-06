<?php

namespace Anper\RedisCollector\Adapter\Predis;

use Anper\RedisCollector\Adapter\Predis\Connection\AggregateConnection;
use Anper\RedisCollector\Adapter\Predis\Connection\Connection;
use Anper\RedisCollector\Adapter\Predis\Connection\NodeConnection;
use Anper\RedisCollector\Adapter\Predis\Exception\InvalidConnectionException;
use Anper\RedisCollector\Adapter\Predis\Format\ResponseFormatter;
use Anper\RedisCollector\ConnectionInterface;
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
     * @var ResponseFormatter
     */
    protected $formatter;

    /**
     * @param RedisCollector $collector
     */
    public function __construct(RedisCollector $collector)
    {
        $this->collector = $collector;
        $this->formatter = new ResponseFormatter();
    }

    /**
     * @param Client $client
     * @return ConnectionInterface
     * @throws InvalidConnectionException
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

        $this->collector->addResponseFormatter($this->formatter);

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
     * @throws InvalidConnectionException
     */
    protected function replaceConnection(Client $client): ConnectionInterface
    {
        $ref = new \ReflectionClass(Client::class);

        foreach ($ref->getProperties() as $property) {
            $property->setAccessible(true);

            $value = $property->getValue($client);

            if (\is_object($value) && $value instanceof PredisConnection) {
                $wrapped = $this->addConnection($value);

                $property->setValue($client, $wrapped);

                return $wrapped;
            }
        }

        throw new InvalidConnectionException(sprintf(
            'Expected client connection instance of "%s"',
            PredisConnection::class
        ));
    }
}
