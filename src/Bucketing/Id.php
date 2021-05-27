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
        $hash = hash('haval192,3', $idToHash);

        $maxIterations = strlen($hash) - 1;
        $maxValueOfX = 2 ** $maxIterations;

        $x = 0;
        for ($i = 0; $i < $maxIterations; ++$i) {
            $x = ($x * 2) + (hexdec($hash[$i]) < 8 ? 0 : 1);
        }

        $x = $x / $maxValueOfX;

        return $x * 100;
    }
}
