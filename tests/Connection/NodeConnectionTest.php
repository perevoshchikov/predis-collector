<?php

namespace Anper\PredisCollector\Tests\Connection;

use Anper\PredisCollector\Connection\NodeConnection;
use PHPUnit\Framework\TestCase;
use Predis\Command\CommandInterface;
use Predis\Connection\NodeConnectionInterface;

class NodeConnectionTest extends TestCase
{
    public function testWrappedMethods()
    {
        $command = $this->createMock(CommandInterface::class);

        $mock = $this->createMock(NodeConnectionInterface::class);
        $mock->expects($this->once())
            ->method('__toString')
            ->willReturn(1)
        ;
        $mock->expects($this->once())
            ->method('getResource')
            ->willReturn(1)
        ;
        $mock->expects($this->once())
            ->method('getParameters')
            ->willReturn(1)
        ;
        $mock->expects($this->once())
            ->method('addConnectCommand')
            ->with($command)
        ;
        $mock->expects($this->once())
            ->method('read')
            ->willReturn(1)
        ;

        $connection = new NodeConnection($mock);

        $this->assertEquals(1, $connection->__toString());
        $this->assertEquals(1, $connection->getResource());
        $this->assertEquals(1, $connection->getParameters());
        $this->assertNull($connection->addConnectCommand($command));
        $this->assertEquals(1, $connection->read());
    }
}
