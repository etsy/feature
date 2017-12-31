<?php

declare(strict_types=1);

namespace PabloJoan\Feature\Contract;

interface Admin
{
    function __construct (string $variant);

    function variant (User $user) : string;
}
