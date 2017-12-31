<?php

declare(strict_types=1);

namespace PabloJoan\Feature\Value;

use PabloJoan\Feature\Contract\{ Internal as InternalContract, User };

class Internal implements InternalContract
{
    private $variant = '';

    function __construct (string $variant) { $this->variant = $variant; }

    function variant (User $user) : string
    {
        return $user->internalIP() ? $this->variant : '';
    }
}
