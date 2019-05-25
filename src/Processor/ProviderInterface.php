<?php

namespace Anper\PredisCollector\Processor;

use Predis\ClientInterface;

interface ProviderInterface
{
    /**
     * @param ClientInterface $client
     * @return ProcessorInterface
     */
    public function register(ClientInterface $client): ProcessorInterface;
}
