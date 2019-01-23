<?php

namespace Anper\PredisCollector\Format\Response;

use Anper\PredisCollector\Format\ResponseFormatterInterface;
use Predis\Response\Status;

/**
 * Class StatusFormatter
 * @package Anper\PredisCollector\Format\Response
 */
class StatusFormatter implements ResponseFormatterInterface
{
    /**
     * @inheritDoc
     */
    public function supports($response): bool
    {
        return \is_object($response) && $response instanceof Status;
    }

    /**
     * @inheritDoc
     */
    public function format($response): string
    {
        return (string) $response;
    }
}
