<?php

declare(strict_types=1);

namespace PabloJoan\Feature\Bucketing;

final class Random implements Type
{
    public function randomIshNumber(string $idToHash = ''): float
    {
        $x = random_int(0, PHP_INT_MAX - 1) / PHP_INT_MAX;
        return $x * 100;
    }
}
