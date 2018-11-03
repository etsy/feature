<?php

declare(strict_types=1);

namespace PabloJoan\Feature\Bucketing\Calculator;

class Random
{
    function number () : float
    {
        $x = random_int(0, PHP_INT_MAX - 1) / PHP_INT_MAX;
        return $x * 100;
    }
}
