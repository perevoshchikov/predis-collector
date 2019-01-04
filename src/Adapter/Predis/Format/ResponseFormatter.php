<?php

namespace Anper\RedisCollector\Adapter\Predis\Format;

use Anper\RedisCollector\Format\ResponseFormatterInterface;
use Predis\Response\Status;

class ResponseFormatter implements ResponseFormatterInterface
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
