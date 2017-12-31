<?php

declare(strict_types=1);

namespace PabloJoan\Feature\Contract;

interface Internal
{
    function __construct (string $variant);

    function variant (User $user) : string;
}
