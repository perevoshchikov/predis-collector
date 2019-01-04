<?php

namespace Anper\RedisCollector\Tests;

use Anper\RedisCollector\Profile;
use PHPUnit\Framework\TestCase;

class ProfileTest extends TestCase
{
    /**
     * @var Profile
     */
    protected $profile;

    protected function setUp()
    {
        $this->profile = new Profile('SET', ['key', 'value']);
    }

    protected function tearDown()
    {
        $this->profile = null;
    }

    public function testMemory()
    {
        $startMemory = 4595112;
        $stopMemory  = 4691048;

        $this->profile->start(0, $startMemory);
        $this->profile->end(0, $stopMemory);

        $this->assertEquals($startMemory, $this->profile->getStartMemory());
        $this->assertEquals($stopMemory, $this->profile->getEndMemory());
        $this->assertEquals($stopMemory - $startMemory, $this->profile->getMemoryUsage());
    }

    public function testTime()
    {
        $startTime = 0.201;
        $stopTime = 0.301;

        $this->profile->start($startTime, 0);
        $this->profile->end($stopTime, 0);

        $this->assertEquals($startTime, $this->profile->getStartTime());
        $this->assertEquals($stopTime, $this->profile->getEndTime());
        $this->assertEquals($stopTime - $startTime, $this->profile->getDuration());
    }

    public function testConstruct()
    {
        $profile = new Profile('SET', ['key', 'value']);

        $this->assertEquals('SET', $profile->getMethod());
        $this->assertEquals(['key', 'value'], $profile->getArguments());
    }

    public function testEmptyException()
    {
        $this->assertTrue($this->profile->isSuccess());
        $this->assertNull($this->profile->getError());
    }

    public function testError()
    {
        $this->profile->setError('Error');

        $this->assertFalse($this->profile->isSuccess());
        $this->assertEquals('Error', $this->profile->getError());
    }

    public function testEmptyResponse()
    {
        $this->assertNull($this->profile->getResponse());
    }

    public function testResponse()
    {
        $response = 'OK';

        $this->profile->setResponse($response);

        $this->assertEquals($response, $this->profile->getResponse());
    }

    public function testBinaryData()
    {
        $profile = new Profile('SET', ["\x00\x81"]);

        $this->assertEquals(['[BINARY DATA]'], $profile->getArguments());
    }
}
