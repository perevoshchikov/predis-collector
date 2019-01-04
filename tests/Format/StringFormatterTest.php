<?php

namespace Anper\RedisCollector\Tests\Format;

use Anper\RedisCollector\Format\Response\StringResponseFormatter;
use PHPUnit\Framework\TestCase;

class StringFormatterTest extends TestCase
{
    public function testDefault()
    {
        $formatter = new StringResponseFormatter();

        $this->assertEquals('string(5) abcde', $formatter->format('abcde'));
    }

    public function testMaxLength()
    {
        $formatter = new StringResponseFormatter(3);

        $this->assertEquals('string(5) abc...', $formatter->format('abcde'));
    }

    public function testWithoutTypehint()
    {
        $formatter = new StringResponseFormatter(100, false);

        $this->assertEquals('abcde', $formatter->format('abcde'));
    }

    public function testSupports()
    {
        $formatter = new StringResponseFormatter();

        $this->assertTrue($formatter->supports('abcde'));
    }

    public function testNotSupports()
    {
        $formatter = new StringResponseFormatter();

        $this->assertFalse($formatter->supports(123));
    }
}
