<?php

declare(strict_types=1);

namespace PabloJoan\Feature\Value;

class PublicUrlOverride
{
    private $on;

    function __construct (bool $on)
    {
        $this->on = $on;
    }

    function variant (string $name, Url $url) : string
    {
        return $this->on ? $url->variant($name) : '';
    }
}
