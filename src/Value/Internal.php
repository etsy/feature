<?php

declare(strict_types=1);

namespace PabloJoan\Feature\Value;

class Internal
{
    private $variant;

    function __construct (string $variant)
    {
        $this->variant = $variant;
    }

    function variant (User $user) : string
    {
        return $user->internalIP() ? $this->variant : '';
    }
}
