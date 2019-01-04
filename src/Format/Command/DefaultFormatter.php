<?php

namespace Anper\RedisCollector\Format\Command;

use Anper\RedisCollector\Format\CommandFormatterInterface;
use Anper\RedisCollector\Profile;

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
        return $profile->getMethod() . ' ' . implode(' ', $profile->getArguments());
    }
}
