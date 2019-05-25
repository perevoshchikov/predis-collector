<?php

namespace Anper\PredisCollector\Processor;

use Predis\Command\CommandInterface;

class TraceableProcessor implements ProcessorInterface
{
    /**
     * @var CommandInterface[]
     */
    protected $commands = [];

    /**
     * @return CommandInterface[]
     */
    public function getCommands(): array
    {
        return $this->commands;
    }

    /**
     * @inheritDoc
     */
    public function process(CommandInterface $command)
    {
        $this->commands[] = $command;
    }
}
