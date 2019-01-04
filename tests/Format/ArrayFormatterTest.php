<?php

namespace Anper\RedisCollector\Tests\Format;

use Anper\RedisCollector\Format\Response\ArrayResponseFormatter;
use PHPUnit\Framework\TestCase;

class ArrayFormatterTest extends TestCase
{
    public function testSupports()
    {
        $formatter = new ArrayResponseFormatter();

        $this->assertTrue($formatter->supports([]));
    }

    public function testNotSupports()
    {
        $formatter = new ArrayResponseFormatter();

        $this->assertFalse($formatter->supports(123));
    }

    public function testWithoutTypehint()
    {
        $formatter = new ArrayResponseFormatter(100, false);

        $this->assertEquals('[1,2,3]', $formatter->format([1, 2, 3]));
    }

    public function testMaxLength()
    {
        $formatter = new ArrayResponseFormatter(4);

        $this->assertEquals('array(3) [1,2...]', $formatter->format([1, 2, 3]));
    }

    public function testDefault()
    {
        $formatter = new ArrayResponseFormatter();

        $this->assertEquals('array(3) [1,2,3]', $formatter->format([1, 2, 3]));
    }
}
