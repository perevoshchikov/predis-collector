<?php

namespace Anper\RedisCollector\Tests\Format\Response;

use Anper\RedisCollector\Format\Response\ArrayFormatter;
use PHPUnit\Framework\TestCase;

class ArrayFormatterTest extends TestCase
{
    public function testSupports()
    {
        $formatter = new ArrayFormatter();

        $this->assertTrue($formatter->supports([]));
    }

    public function testNotSupports()
    {
        $formatter = new ArrayFormatter();

        $this->assertFalse($formatter->supports(123));
    }

    public function testWithoutTypehint()
    {
        $formatter = new ArrayFormatter(100, false);

        $this->assertEquals('[1,2,3]', $formatter->format([1, 2, 3]));
    }

    public function testMaxLength()
    {
        $formatter = new ArrayFormatter(4);

        $this->assertEquals('array(3) [1,2...]', $formatter->format([1, 2, 3]));
    }

    public function testDefault()
    {
        $formatter = new ArrayFormatter();

        $this->assertEquals('array(3) [1,2,3]', $formatter->format([1, 2, 3]));
    }
}
