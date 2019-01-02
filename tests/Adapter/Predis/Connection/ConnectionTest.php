<?php

namespace Anper\RedisCollector\Tests\Adapter\Predis\Connection;

use Anper\RedisCollector\Adapter\Predis\Connection\Connection;
use Anper\RedisCollector\Statement;
use PHPUnit\Framework\TestCase;
use Predis\Command\CommandInterface;
use Predis\Connection\ConnectionInterface;
use Predis\Response\Error;
use Predis\Response\Status;

class ConnectionTest extends TestCase
{
    /**
     * @var Connection
     */
    protected $connection;

    protected function setUp()
    {
        $mock = $this->createMock(ConnectionInterface::class);

        $this->connection = new Connection($mock);
    }

    protected function tearDown()
    {
        $this->connection = null;
    }

    public function testGetSourceConnection()
    {
        $mock = $this->createMock(ConnectionInterface::class);

        $connection = new Connection($mock);

        $this->assertEquals($mock, $connection->getSourceConnection());
    }

    public function testWrappedMethods()
    {
        $command = $this->createMock(CommandInterface::class);
        $command->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn('SET');
        $command->expects($this->atLeastOnce())
            ->method('getArguments')
            ->willReturn([]);

        $mock = $this->createMock(ConnectionInterface::class);
        $mock->expects($this->once())
            ->method('connect')
        ;
        $mock->expects($this->once())
            ->method('disconnect')
        ;
        $mock->expects($this->once())
            ->method('isConnected')
            ->willReturn(true)
        ;
        $mock->expects($this->once())
            ->method('writeRequest')
            ->with($command)
        ;
        $mock->expects($this->once())
            ->method('readResponse')
            ->with($command)
            ->willReturn(1)
        ;
        $mock->expects($this->once())
            ->method('executeCommand')
            ->with($command)
            ->willReturn(1)
        ;

        $connection = new Connection($mock);

        $this->assertNull($connection->connect());
        $this->assertNull($connection->disconnect());
        $this->assertTrue($connection->isConnected());
        $this->assertEquals(1, $connection->executeCommand($command));
        $this->assertNull($connection->writeRequest($command));
        $this->assertEquals(1, $connection->readResponse($command));
    }

    public function testAddStatement()
    {
        $statement = new Statement('SET', ['key', 'value']);

        $this->connection->addExecutedStatement($statement);

        $this->assertContains($statement, $this->connection->getExecutedStatements());
    }

    public function testSupports()
    {
        $this->assertFalse($this->connection->supports(1));
        $this->assertTrue($this->connection->supports(new Status('OK')));
    }

    public function testFormat()
    {
        $this->assertEquals('OK', $this->connection->format(new Status('OK')));
    }

    public function testValidStatement()
    {
        $statement = $this->getStatement('OK');

        $this->assertEquals('OK', $statement->getResponse());
        $this->assertNull($statement->getException());
        $this->assertEquals('', $statement->getErrorMessage());
        $this->assertEquals($statement->getMethod(), 'SET');
        $this->assertEquals($statement->getArguments(), ['key', 'value']);
        $this->assertTrue($statement->getDuration() > 0);
        $this->assertTrue($statement->getStartTime() > 0);
        $this->assertTrue($statement->getEndTime() > 0);
        $this->assertTrue($statement->getMemoryUsage() > 0);
        $this->assertTrue($statement->getStartMemory() > 0);
        $this->assertTrue($statement->getEndMemory() > 0);
    }

    public function testErrorStatement()
    {
        $statement = $this->getStatement(new Error('ERROR'));

        $this->assertEquals('ERROR', $statement->getErrorMessage());
        $this->assertInstanceOf(\Exception::class, $statement->getException());
    }

    public function testExceptionStatement()
    {
        $this->expectException(\Exception::class);

        $statement = $this->getStatement('OK', new \Exception('EXCEPTION'));

        $this->assertEquals('EXCEPTION', $statement->getErrorMessage());
        $this->assertInstanceOf(\Exception::class, $statement->getException());
    }

    protected function getStatement($response, \Exception $exception = null)
    {
        $command = $this->createMock(CommandInterface::class);
        $command->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn('SET');
        $command->expects($this->atLeastOnce())
            ->method('getArguments')
            ->willReturn(['key', 'value']);

        $mock = $this->createMock(ConnectionInterface::class);
        $method = $mock->expects($this->once())
            ->method('executeCommand')
            ->with($command)
            ->willReturn($response)
        ;

        if ($exception) {
            $method->willThrowException($exception);
        }

        $connection = new Connection($mock);
        $result = $connection->executeCommand($command);

        $statements = $connection->getExecutedStatements();

        $this->assertCount(1, $statements);
        $this->assertEquals($response, $result);

        return $statements[0];
    }
}
