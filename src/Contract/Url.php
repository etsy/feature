<?php

declare(strict_types=1);

namespace PabloJoan\Feature\Contract;

interface Url
{
    function __construct (string $url);

    function variant (Name $name) : string;
}
