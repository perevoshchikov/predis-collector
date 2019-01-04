<?php

namespace Anper\RedisCollector\Tests\Adapter\Predis;

use Anper\RedisCollector\Adapter\Predis\Connection\AggregateConnection;
use Anper\RedisCollector\Adapter\Predis\Connection\Connection;
use Anper\RedisCollector\Adapter\Predis\Connection\NodeConnection;
use Anper\RedisCollector\Adapter\Predis\Exception\InvalidConnectionException;
use Anper\RedisCollector\Adapter\Predis\PredisAdapter;
use Anper\RedisCollector\RedisCollector;
use PHPUnit\Framework\TestCase;
use Predis\Client;
use Predis\Connection\AggregateConnectionInterface;
use Predis\Connection\ConnectionInterface;
use Predis\Connection\NodeConnectionInterface;

class PredisAdapterTest extends TestCase
{
    /**
     * @var RedisCollector
     */
    protected $collector;

    /**
     * @var PredisAdapter
     */
    protected $adapter;

    protected function setUp()
    {
        $this->collector = new RedisCollector();
        $this->adapter = new PredisAdapter($this->collector);
    }

    protected function tearDown()
    {
        $this->collector = null;
        $this->adapter = null;
    }

    public function testAddClient()
    {
        $connection = $this->createMock(ConnectionInterface::class);
        $client = new Client($connection);

        /** @var Connection $wrapped */
        $wrapped = $this->adapter->addClient($client);

        $getter = \Closure::bind(function ($client) {
            return $client->connection;
        }, null, $client);

        $this->assertEquals($wrapped, $getter($client));
    }

    public function testAddConnection()
    {
        $connection = $this->createMock(ConnectionInterface::class);

        $this->assertCount(1, $this->collector->getResponseFormatters());

        /** @var Connection $wrapped */
        $wrapped = $this->adapter->addConnection($connection);

        $this->assertCount(2, $this->collector->getResponseFormatters());

        $this->assertInstanceOf(Connection::class, $wrapped);
        $this->assertContains($wrapped, $this->collector->getConnections());
        $this->assertEquals($connection, $wrapped->getSourceConnection());
    }

    public function testWrapConnection()
    {
        $this->assertWrappedConnection(
            ConnectionInterface::class,
            Connection::class
        );
        $this->assertWrappedConnection(
            NodeConnectionInterface::class,
            NodeConnection::class
        );
        $this->assertWrappedConnection(
            AggregateConnectionInterface::class,
            AggregateConnection::class
        );
    }

    public function testInvalidConnection()
    {
        $this->expectException(InvalidConnectionException::class);

        $connection = $this->createMock(ConnectionInterface::class);
        $client = new Client($connection);

        $setter = \Closure::bind(function ($client, $connection) {
            $client->connection = $connection;
        }, null, $client);

        $setter($client, null);

        $this->adapter->addClient($client);
    }

    protected function assertWrappedConnection(string $connection, string $wrapped)
    {
        $conn = $this->createMock($connection);
        $wrap = $this->adapter->wrapConnection($conn);

        $this->assertInstanceOf($wrapped, $wrap);
        $this->assertEquals($conn, $wrap->getSourceConnection());
    }
}
