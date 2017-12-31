<?php

declare(strict_types=1);

namespace PabloJoan\Feature\Value;

use PabloJoan\Feature\Contract\Source as SourceContract;

class Source implements SourceContract
{
    private $source = '';

    function __construct (string $source) { $this->source = $source; }

    function variant () : string { return $this->source; }
}
