<?php

declare(strict_types=1);

namespace PabloJoan\Feature\Bucketing;

final class Id implements Type
{
    /**
     * Map a hex value to the half-open interval between 0 and 1 while
     * preserving uniformity of the input distribution.
     */
    public function randomIshNumber(string $idToHash = ''): float
    {
        $hash = hash('ripemd256', $idToHash);

        $x = 0;
        for ($i = 0; $i < 63; ++$i) {
            $x = ($x * 2) + (hexdec($hash[$i]) < 8 ? 0 : 1);
        }

        $x = $x / PHP_INT_MAX;

        return $x * 100;
    }
}
