<?php

declare(strict_types=1);

namespace PabloJoan\Feature\Bucketing;

interface Type
{
    /**
     * A random-ish number between 0 and 100 based on the feature name and $id
     * unless we are bucketing completely at random
     */
    public function randomIshNumber(string $idToHash = ''): float;
}
