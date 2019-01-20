<?php

namespace Anper\PredisCollector\Format\Command;

use Anper\PredisCollector\Format\CommandFormatterInterface;
use Anper\PredisCollector\Profile;

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
