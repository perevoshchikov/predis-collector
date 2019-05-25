<?php

namespace Anper\PredisCollector\Tests;

use Predis\Profile\RedisProfile;

class RedisProfileMock extends RedisProfile
{
    /**
     * @inheritDoc
     */
    protected function getSupportedCommands()
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getVersion()
    {
        return 1;
    }
}
