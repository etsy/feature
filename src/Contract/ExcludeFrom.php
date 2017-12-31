<?php

declare(strict_types=1);

namespace PabloJoan\Feature\Contract;

interface ExcludeFrom
{
    function __construct (array $excludeFrom);

    function variant (User $user) : string;
}
