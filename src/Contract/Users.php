<?php

declare(strict_types=1);

namespace PabloJoan\Feature\Contract;

interface Users
{
    function __construct (array $stanza);

    function variant (User $user) : string;
}
