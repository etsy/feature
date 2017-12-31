<?php

declare(strict_types=1);

namespace PabloJoan\Feature\Contract;

interface Description
{
    function __construct (string $description);

    function __toString () : string;
}
