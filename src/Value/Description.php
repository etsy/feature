<?php

declare(strict_types=1);

namespace PabloJoan\Feature\Value;

class Description
{
    private $description = '';

    function __construct (string $description)
    {
        $this->description = $description;
    }

    function __toString () : string { return $this->description; }
}
