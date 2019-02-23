<?php

namespace Anper\PredisCollector\Format\Command;

use Anper\PredisCollector\Format\CommandFormatterInterface;
use Anper\PredisCollector\Profile;

/**
 * Class DefaultFormatter
 * @package Anper\PredisCollector\Format\Command
 */
class DefaultFormatter implements CommandFormatterInterface
{
    /**
     * @inheritDoc
     */
    public function supports(Profile $profile): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function format(Profile $profile): string
    {
        $arguments = \array_map(function ($value) {
            return $value === '' ? '""' : $value;
        }, $profile->getArguments());

        return $profile->getMethod() . ' ' . implode(' ', $arguments);
    }
}
