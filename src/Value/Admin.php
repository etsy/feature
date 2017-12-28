<?php

declare(strict_types=1);

namespace PabloJoan\Feature\Value;

class Admin
{
    private $variant = '';

    function __construct (string $variant) { $this->variant = $variant; }

    function variant (User $user) : string
    {
        return $user->isAdmin() ? $this->variant : '';
    }
}
