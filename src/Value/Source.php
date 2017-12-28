<?php

declare(strict_types=1);

namespace PabloJoan\Feature\Value;

class Source
{
    private $source = '';

    function __construct (string $source) { $this->source = $source; }

    function variant () : string { return $this->source; }
}
