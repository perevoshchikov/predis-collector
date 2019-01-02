<?php

namespace Anper\RedisCollector\Tests;

use Anper\RedisCollector\Statement;
use PHPUnit\Framework\TestCase;

class StatementTest extends TestCase
{
    /**
     * @var Statement
     */
    protected $statement;

    protected function setUp()
    {
        $this->statement = new Statement('SET', ['key', 'value']);
    }

    protected function tearDown()
    {
        $this->statement = null;
    }

    public function testMemory()
    {
        $startMemory = 4595112;
        $stopMemory  = 4691048;

        $this->statement->start(0, $startMemory);
        $this->statement->end(null, 0, $stopMemory);

        $this->assertEquals($startMemory, $this->statement->getStartMemory());
        $this->assertEquals($stopMemory, $this->statement->getEndMemory());
        $this->assertEquals($stopMemory - $startMemory, $this->statement->getMemoryUsage());
    }

    public function testTime()
    {
        $startTime = 0.201;
        $stopTime = 0.301;

        $this->statement->start($startTime, 0);
        $this->statement->end(null, $stopTime, 0);

        $this->assertEquals($startTime, $this->statement->getStartTime());
        $this->assertEquals($stopTime, $this->statement->getEndTime());
        $this->assertEquals($stopTime - $startTime, $this->statement->getDuration());
    }

    public function testConstruct()
    {
        $statement = new Statement('SET', ['key', 'value']);

        $this->assertEquals('SET', $statement->getMethod());
        $this->assertEquals(['key', 'value'], $statement->getArguments());
    }

    public function testEmptyException()
    {
        $this->assertTrue($this->statement->isSuccess());
        $this->assertEquals('', $this->statement->getErrorMessage());
        $this->assertNull($this->statement->getException());
    }

    public function testException()
    {
        $exception = new \Exception('Error');

        $this->statement->end($exception);

        $this->assertFalse($this->statement->isSuccess());
        $this->assertEquals($exception->getMessage(), $this->statement->getErrorMessage());
        $this->assertEquals($exception, $this->statement->getException());
    }

    public function testEmptyResponse()
    {
        $this->assertNull($this->statement->getResponse());
    }

    public function testResponse()
    {
        $response = 'OK';

        $this->statement->setResponse($response);

        $this->assertEquals($response, $this->statement->getResponse());
    }

    public function setBinaryData()
    {
        $statement = new Statement('SET', ['\x00\x81']);

        $this->assertEquals(['[BINARY DATA]'], $statement->getArguments());
    }
}
