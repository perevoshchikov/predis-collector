<?php

namespace Anper\RedisCollector\Format\Response;

use Anper\RedisCollector\Format\ResponseFormatterInterface;

/**
 * Class StringResponseFormatter
 * @package Anper\RedisCollector\Format
 */
class StringResponseFormatter implements ResponseFormatterInterface
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
