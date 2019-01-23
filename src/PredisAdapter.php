<?php

namespace Anper\PredisCollector;

use Anper\PredisCollector\Connection\AggregateConnection;
use Anper\PredisCollector\Connection\Connection;
use Anper\PredisCollector\Connection\ConnectionInterface;
use Anper\PredisCollector\Connection\NodeConnection;
use Anper\PredisCollector\Exception\InvalidConnectionException;
use Predis\Client;
use Predis\Connection\AggregateConnectionInterface;
use Predis\Connection\ConnectionInterface as PredisConnection;
use Predis\Connection\NodeConnectionInterface;

/**
 * Class PredisAdapter
 * @package Anper\PredisCollector
 */
class PredisAdapter
{
    /**
     * @var PredisCollector
     */
    protected $collector;

    /**
     * @param PredisCollector $collector
     */
    public function __construct(PredisCollector $collector)
    {
        $this->collector = $collector;
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
