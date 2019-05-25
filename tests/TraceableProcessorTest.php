<?php

namespace Anper\PredisCollector\Tests;

use Anper\PredisCollector\Processor\TraceableProcessor;
use PHPUnit\Framework\TestCase;
use Predis\Command\CommandInterface;

class TraceableProcessorTest extends TestCase
{
    public function testProcessAndGetCommands()
    {
        $processor = new TraceableProcessor();

        $command = $this->createMock(CommandInterface::class);

        $processor->process($command);

        $this->assertEquals([$command], $processor->getCommands());
    }
}
