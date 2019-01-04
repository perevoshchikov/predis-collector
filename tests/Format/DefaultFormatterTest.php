<?php

namespace Anper\RedisCollector\Tests\Format;

use Anper\RedisCollector\Format\Response\DefaultFormatter;
use PHPUnit\Framework\TestCase;

class DefaultFormatterTest extends TestCase
{
    /**
     * @var DefaultFormatter
     */
    protected $formatter;

    protected function setUp()
    {
        $this->formatter = new DefaultFormatter();
    }

    protected function tearDown()
    {
        $this->formatter = null;
    }

    public function testSupports()
    {
        $this->assertTrue($this->formatter->supports(1));
    }

    /**
     * @dataProvider provider
     */
    public function testFormat($expected, $value)
    {
        $this->assertEquals($expected, $this->formatter->format($value));
    }

    public function provider()
    {
        return [
            [\DateTime::class, new \DateTime()],
            ['string(3)', 'abc'],
            [1, 1],
            [1.5, 1.5],
            [true, true],
            [false, false],
            ['array(3)', [1, 2, 3]],
            ['NULL', null],
            ['resource(stream)', \opendir(__DIR__)],
        ];
    }
}
