<?php

declare(strict_types=1);

namespace PabloJoan\Feature\Bucketing;

interface Type
{
    /**
     * A hash that maps the given string to a number between 0 and 100 
     * unless we are bucketing completely at random
     */
    public function strToIntHash(string $idToHash): int;
}
