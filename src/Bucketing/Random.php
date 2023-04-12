<?php

declare(strict_types=1);

namespace PabloJoan\Feature\Bucketing;

use Random\Randomizer;
use Random\Engine\Xoshiro256StarStar;

final readonly class Random implements Type
{
    private Randomizer $randomizer;

    public function __construct()
    {
        $this->randomizer = new Randomizer(new Xoshiro256StarStar());
    }

    public function strToIntHash(string $idToHash = ''): float
    {
        $decimal = $this->randomizer->getInt(0, PHP_INT_MAX) / PHP_INT_MAX;
        return $decimal * 100;
    }
}
