<?php

declare(strict_types=1);

namespace PabloJoan\Feature\Bucketing\Calculator;

class Id
{
    /**
     * Map a hex value to the half-open interval between 0 and 1 while
     * preserving uniformity of the input distribution.
     */
    function number (string $idToHash) : float
    {
        $hash = hash('haval192,3', $idToHash);
        $x = 0;
        for ($i = 0; $i < 47; ++$i) {
            $x = ($x * 2) + (hexdec($hash[$i]) < 8 ? 0 : 1);
        }

        $x = $x / 140737488355328; // ( 2 ** 47 ) is the max value of $x

        return $x * 100;
    }
}
