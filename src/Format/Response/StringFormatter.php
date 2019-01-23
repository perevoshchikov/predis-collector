<?php

namespace Anper\PredisCollector\Format\Response;

use Anper\PredisCollector\Format\ResponseFormatterInterface;

/**
 * Class StringFormatter
 * @package Anper\PredisCollector\Format\Response
 */
class StringFormatter implements ResponseFormatterInterface
{
    /**
     * @var int
     */
    protected $maxLength;

    /**
     * @var bool
     */
    protected $typeHint;

    /**
     * @param int $maxLength
     * @param bool $typeHint
     */
    public function __construct(int $maxLength = 100, bool $typeHint = true)
    {
        $this->maxLength = $maxLength;
        $this->typeHint = $typeHint;
    }

    /**
     * @inheritDoc
     */
    public function supports($response): bool
    {
        return \is_string($response);
    }

    /**
     * @inheritDoc
     */
    public function format($response): string
    {
        $length = \strlen($response);

        if ($length > $this->maxLength) {
            $response = substr($response, 0, $this->maxLength) . '...';
        }

        if ($this->typeHint) {
            $response = 'string(' . $length . ') ' . $response;
        }

        return $response;
    }
}
