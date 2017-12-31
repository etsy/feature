<?php

declare(strict_types=1);

namespace PabloJoan\Feature\Contract;

interface Name
{
    function __construct (string $name);

    function __toString () : string;
}
