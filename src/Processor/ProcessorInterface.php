<?php

namespace Anper\PredisCollector\Processor;

use Predis\Command\CommandInterface;

interface ProcessorInterface extends \Predis\Command\Processor\ProcessorInterface
{
    /**
     * @return CommandInterface[]
     */
    public function getCommands(): array;
}
