<?php

namespace Anper\RedisCollector\Tests\Format\Command;

use Anper\RedisCollector\Format\Command\DefaultFormatter;
use Anper\RedisCollector\Profile;
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
        $this->assertTrue($this->formatter->supports(new Profile('SET', [])));
    }

    public function testFormat()
    {
        $this->assertEquals(
            'SET key value',
            $this->formatter->format(new Profile('SET', ['key', 'value']))
        );
    }
}
