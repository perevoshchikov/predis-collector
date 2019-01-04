<?php

namespace Anper\RedisCollector\Tests\Adapter\Predis\Format;

use Anper\RedisCollector\Adapter\Predis\Format\ResponseFormatter;
use PHPUnit\Framework\TestCase;
use Predis\Response\Status;

class ResponseFormatterTest extends TestCase
{
    /**
     * @var ResponseFormatter
     */
    protected $formatter;

    protected function setUp()
    {
        $this->formatter = new ResponseFormatter();
    }

    protected function tearDown()
    {
        $this->formatter = null;
    }

    public function testSupports()
    {
        $this->assertFalse($this->formatter->supports(1));
        $this->assertTrue($this->formatter->supports(new Status('OK')));
    }

    public function testFormat()
    {
        $this->assertEquals('OK', $this->formatter->format(new Status('OK')));
    }
}
