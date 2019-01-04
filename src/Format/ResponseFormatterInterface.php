<?php

namespace Anper\RedisCollector\Format;

interface ResponseFormatterInterface
{
    /**
     * @param mixed $response
     * @return bool
     */
    public function supports($response): bool;

    /**
     * @param mixed $response
     * @return string
     */
    public function format($response): string;
}
