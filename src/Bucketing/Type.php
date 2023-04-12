<?php

declare(strict_types=1);

namespace PabloJoan\Feature\Bucketing;

interface Type
{
    /**
     * A hash number between 0 and 100 based on an id string
     * unless we are bucketing completely at random
     */
    public function strToIntHash(string $idToHash = ''): float;
}
