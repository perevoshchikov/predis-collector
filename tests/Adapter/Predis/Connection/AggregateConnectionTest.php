<?php

namespace Anper\RedisCollector\Tests\Adapter\Predis\Connection;

use Anper\RedisCollector\Adapter\Predis\Connection\AggregateConnection;
use Anper\RedisCollector\Adapter\Predis\Connection\NodeConnection;
use Anper\RedisCollector\Adapter\Predis\PredisAdapter;
use Anper\RedisCollector\RedisCollector;
use PHPUnit\Framework\TestCase;
use Predis\Command\CommandInterface;
use Predis\Connection\AggregateConnectionInterface;
use Predis\Connection\NodeConnectionInterface;

class AggregateConnectionTest extends TestCase
{
    public function testWrappedMethods()
    {
        $node = $this->createMock(NodeConnectionInterface::class);

        $mock = $this->createMock(AggregateConnectionInterface::class);
        $mock->expects($this->once())
            ->method('add')
            ->with($node)
        ;
        $mock->expects($this->once())
            ->method('remove')
            ->with($node)
            ->willReturn(true)
        ;

        $connection = new AggregateConnection($mock, new PredisAdapter(new RedisCollector()));

        $this->assertTrue($connection->remove($node));
        $this->assertNull($connection->add($node));
    }

    public function testGetConnection()
    {
        $node = $this->createMock(NodeConnectionInterface::class);
        $command = $this->createMock(CommandInterface::class);

        $connectionID = 'master';

        $mock = $this->createMock(AggregateConnectionInterface::class);

        $mock->expects($this->once())
            ->method('getConnection')
            ->with($command)
            ->willReturn($node)
        ;
        $mock->expects($this->once())
            ->method('getConnectionById')
            ->with($connectionID)
            ->willReturn($node)
        ;

        $collector = new RedisCollector();

        $connection = new AggregateConnection($mock, new PredisAdapter($collector));

        /** @var NodeConnection $conn */
        $conn = $connection->getConnection($command);

        $this->assertInstanceOf(NodeConnectionInterface::class, $conn);
        $this->assertEquals($node, $conn->getSourceConnection());
        $this->assertContains($conn, $collector->getConnections());

        /** @var NodeConnection $conn */
        $conn = $connection->getConnectionById($connectionID);

        $this->assertInstanceOf(NodeConnectionInterface::class, $conn);
        $this->assertEquals($node, $conn->getSourceConnection());
        $this->assertContains($conn, $collector->getConnections());
    }

    public function testGetEmptyConnection()
    {
        $command = $this->createMock(CommandInterface::class);

        $connectionID = 'master';

        $mock = $this->createMock(AggregateConnectionInterface::class);

        $mock->expects($this->once())
            ->method('getConnection')
            ->with($command)
            ->willReturn(null)
        ;
        $mock->expects($this->once())
            ->method('getConnectionById')
            ->with($connectionID)
            ->willReturn(null)
        ;

        $connection = new AggregateConnection($mock, new PredisAdapter(new RedisCollector()));

        $this->assertNull($connection->getConnection($command));
        $this->assertNull($connection->getConnectionById($connectionID));
    }
}
