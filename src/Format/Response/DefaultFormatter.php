<?php

namespace Anper\PredisCollector\Format\Response;

use Anper\PredisCollector\Format\ResponseFormatterInterface;

class DefaultFormatter implements ResponseFormatterInterface
{
    /**
     * @inheritDoc
     */
    public function supports($response): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function format($response): string
    {
        $type = \gettype($response);

        switch ($type) {
            case 'object':
                return \get_class($response);
            case 'string':
                return 'string(' . \strlen($response) . ')';
            case 'integer':
            case 'boolean':
            case 'double':
                return (string) $response;
            case 'array':
                return 'array(' . \count($response) . ')';
            case 'resource':
                return 'resource(' . \get_resource_type($response) . ')';
            default:
                return $type;
        }
    }
}
