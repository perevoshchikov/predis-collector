<?php

namespace Anper\PredisCollector\Tests\Format\Response;

use Anper\PredisCollector\Format\Response\StatusFormatter;
use PHPUnit\Framework\TestCase;
use Predis\Response\Status;

class StatusFormatterTest extends TestCase
{
    /**
     * @var StatusFormatter
     */
    protected $formatter;

    protected function setUp()
    {
        $this->formatter = new StatusFormatter();
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
