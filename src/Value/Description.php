<?php

declare(strict_types=1);

namespace PabloJoan\Feature\Value;

use PabloJoan\Feature\Contract\Description as DescriptionContract;

class Description implements DescriptionContract
{
    private $description = '';

    function __construct (string $description)
    {
        $this->description = $description;
    }

    function __toString () : string { return $this->description; }
}
