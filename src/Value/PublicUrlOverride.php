<?php

declare(strict_types=1);

namespace PabloJoan\Feature\Value;

use PabloJoan\Feature\Contract\{
    PublicUrlOverride as PublicUrlOverrideContract,
    Name,
    Url
};

class PublicUrlOverride implements PublicUrlOverrideContract
{
    private $on = false;

    function __construct (bool $on) { $this->on = $on; }

    function variant (Name $name, Url $url) : string
    {
        return $this->on ? $url->variant($name) : '';
    }
}
