<?php

namespace Anper\PredisCollector\Format;

use Anper\PredisCollector\Profile;

interface CommandFormatterInterface
{
    /**
     * @param Profile $profile
     * @return bool
     */
    public function supports(Profile $profile): bool;

    /**
     * @param Profile $profile
     * @return string
     */
    public function format(Profile $profile): string;
}
