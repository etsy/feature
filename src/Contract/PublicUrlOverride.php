<?php

declare(strict_types=1);

namespace PabloJoan\Feature\Contract;

interface PublicUrlOverride
{
    function __construct (bool $on);

    function variant (Name $name, Url $url) : string;
}
