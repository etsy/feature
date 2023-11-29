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

    public function strToIntHash(string $idToHash): int
    {
        return $this->randomizer->getInt(0, 99);
    }
}
