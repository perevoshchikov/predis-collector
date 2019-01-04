<?php

namespace Anper\RedisCollector\Format\Response;

use Anper\RedisCollector\Format\ResponseFormatterInterface;

/**
 * Class ArrayFormatter
 * @package Anper\RedisCollector\Format
 */
class ArrayFormatter implements ResponseFormatterInterface
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
        return \is_array($response);
    }

    /**
     * @inheritDoc
     */
    public function format($response): string
    {
        $result = (string) \json_encode($response);

        $length = \strlen($result);

        if ($length > $this->maxLength) {
            $result = substr($result, 0, $this->maxLength) . '...' . $result[$length - 1];
        }

        if ($this->typeHint) {
            $result = 'array(' . \count($response). ') ' . $result;
        }

        return $result;
    }
}
