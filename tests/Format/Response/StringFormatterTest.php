<?php

namespace Anper\PredisCollector\Tests\Format\Response;

use Anper\PredisCollector\Format\Response\StringFormatter;
use PHPUnit\Framework\TestCase;

class StringFormatterTest extends TestCase
{
    public function testDefault()
    {
        $formatter = new StringFormatter();

        $this->assertEquals('string(5) abcde', $formatter->format('abcde'));
    }

    public function testMaxLength()
    {
        $formatter = new StringFormatter(3);

        $this->assertEquals('string(5) abc...', $formatter->format('abcde'));
    }

    public function testWithoutTypehint()
    {
        $formatter = new StringFormatter(100, false);

        $this->assertEquals('abcde', $formatter->format('abcde'));
    }

    public function testSupports()
    {
        $formatter = new StringFormatter();

        $this->assertTrue($formatter->supports('abcde'));
    }

    public function testNotSupports()
    {
        $formatter = new StringFormatter();

        $this->assertFalse($formatter->supports(123));
    }
}
