<?php

namespace Anper\PredisCollector\Tests;

use Anper\PredisCollector\Connection\AggregateConnection;
use Anper\PredisCollector\Connection\Connection;
use Anper\PredisCollector\Connection\NodeConnection;
use Anper\PredisCollector\Exception\InvalidConnectionException;
use Anper\PredisCollector\PredisAdapter;
use Anper\PredisCollector\PredisCollector;
use PHPUnit\Framework\TestCase;
use Predis\Client;
use Predis\Connection\AggregateConnectionInterface;
use Predis\Connection\ConnectionInterface;
use Predis\Connection\NodeConnectionInterface;

class PredisAdapterTest extends TestCase
{
    /**
     * @var PredisCollector
     */
    protected $collector;

    /**
     * @var PredisAdapter
     */
    protected $adapter;

    protected function setUp()
    {
        $this->collector = new PredisCollector();
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

        /** @var \Anper\PredisCollector\Connection\Connection $wrapped */
        $wrapped = $this->adapter->addClient($client);

        $this->assertEquals($wrapped, $client->getConnection());
    }

    public function testAddConnection()
    {
        $connection = $this->createMock(ConnectionInterface::class);

        /** @var \Anper\PredisCollector\Connection\Connection $wrapped */
        $wrapped = $this->adapter->addConnection($connection);

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

        $client = $this->createMock(Client::class);

        $this->adapter->addClient($client);
    }

    protected function assertWrappedConnection(string $connection, string $wrapped)
    {
        /** @var ConnectionInterface $conn */
        $conn = $this->createMock($connection);
        /** @var \Anper\RedisCollector\Connection\Connection $wrap */
        $wrap = $this->adapter->wrapConnection($conn);

        $this->assertInstanceOf($wrapped, $wrap);
        $this->assertEquals($conn, $wrap->getSourceConnection());
    }
}
