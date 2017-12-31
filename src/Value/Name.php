<?php

declare(strict_types=1);

namespace PabloJoan\Feature\Value;

use PabloJoan\Feature\Contract\Name as NameContract;

class Name implements NameContract
{
    private $name = '';

    function __construct (string $name)
    {
        if (!$name) throw new \Exception('all features must have a name.');
        $this->name = $name;
    }

    function __toString () : string { return $this->name; }
}
