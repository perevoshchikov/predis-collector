<?php

namespace Anper\RedisCollector\Tests\Adapter\Predis\Connection;

use Anper\RedisCollector\Adapter\Predis\Connection\Connection;
use Anper\RedisCollector\Profile;
use PHPUnit\Framework\TestCase;
use Predis\Command\CommandInterface;
use Predis\Connection\ConnectionInterface;
use Predis\Response\Error;

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

    public function testAddProfile()
    {
        $profile = new Profile('SET', ['key', 'value']);

        $this->connection->addProfile($profile);

        $this->assertContains($profile, $this->connection->getProfiles());
    }

    public function testValidProfile()
    {
        $profile = $this->getProfile('OK');

        $this->assertEquals('OK', $profile->getResponse());
        $this->assertNull($profile->getError());
        $this->assertEquals($profile->getMethod(), 'SET');
        $this->assertEquals($profile->getArguments(), ['key', 'value']);
        $this->assertTrue($profile->getDuration() > 0);
        $this->assertTrue($profile->getStartTime() > 0);
        $this->assertTrue($profile->getEndTime() > 0);
        $this->assertTrue($profile->getMemoryUsage() > 0);
        $this->assertTrue($profile->getStartMemory() > 0);
        $this->assertTrue($profile->getEndMemory() > 0);
    }

    public function testErrorProfile()
    {
        $profile = $this->getProfile(new Error('ERROR'));

        $this->assertEquals('ERROR', $profile->getError());
    }

    public function testExceptionProfile()
    {
        $this->expectException(\Exception::class);

        $profile = $this->getProfile('OK', new \Exception('EXCEPTION'));

        $this->assertEquals('EXCEPTION', $profile->getError());
    }

    public function testMagicMethods()
    {
        $mock = $this->createMock(ConnectionMock::class);
        $mock->expects($this->once())
            ->method('__set')
            ->with('foo', 'bar');

        $mock->expects($this->once())
            ->method('__get')
            ->with('foo');

        $mock->expects($this->once())
            ->method('__call')
            ->with('foo', ['bar']);

        $mock->expects($this->once())
            ->method('__toString')
            ->willReturn('id');

        $connection = new Connection($mock);

        $connection->foo = 'bar';
        $connection->foo;
        $connection->foo('bar');
        $this->assertEquals('id', $connection->getConnectionId());
    }

    protected function getProfile($response, \Exception $exception = null)
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

        $profiles = $connection->getProfiles();

        $this->assertCount(1, $profiles);
        $this->assertEquals($response, $result);

        return $profiles[0];
    }
}
