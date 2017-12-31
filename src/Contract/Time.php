<?php

declare(strict_types=1);

namespace PabloJoan\Feature\Contract;

interface Time
{
    function __construct (string $start, string $end);

    function variant () : string;
}
