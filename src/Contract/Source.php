<?php

declare(strict_types=1);

namespace PabloJoan\Feature\Contract;

interface Source
{
    function __construct (string $source);

    function variant () : string;
}
