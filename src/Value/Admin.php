<?php

declare(strict_types=1);

namespace PabloJoan\Feature\Value;

use PabloJoan\Feature\Contract\{ Admin as AdminContract, User };

class Admin implements AdminContract
{
    private $variant = '';

    function __construct (string $variant) { $this->variant = $variant; }

    function variant (User $user) : string
    {
        return $user->isAdmin() ? $this->variant : '';
    }
}
