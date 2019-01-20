<?php

namespace Anper\PredisCollector\Tests\Format\Command;

use Anper\PredisCollector\Format\Command\HighlightFormatter;
use Anper\PredisCollector\Profile;
use PHPUnit\Framework\TestCase;

class HighlightFormatterTest extends TestCase
{
    /**
     * @var HighlightFormatter
     */
    protected $formatter;

    protected function setUp()
    {
        $this->formatter = new HighlightFormatter();
    }

    protected function tearDown()
    {
        $this->formatter = null;
    }

    public function testSupports()
    {
        $this->assertTrue($this->formatter->supports(new Profile('SET')));
    }

    public function testDefaultStyles()
    {
        $this->assertEquals([
            'method' => 'font-weight: bold; color: #333;',
            'arguments' => [
                'color: #d14;',
            ],
        ], $this->formatter->getStyles());
    }

    public function testSetStyles()
    {
        $styles = ['foo' => 'bar'];

        $this->assertNull($this->formatter->setStyles($styles));
        $this->assertEquals($styles, $this->formatter->getStyles());
    }

    public function testFormat()
    {
        $profile = new Profile('SET', ['foo', 'bar']);
        $asserts = [
            '<span style="font-weight: bold; color: #333;">SET</span>',
            '<span style="color: #d14;">foo</span>',
            '<span style="">bar</span>'
        ];

        $this->assertEquals(implode(' ', $asserts), $this->formatter->format($profile));
    }
}
