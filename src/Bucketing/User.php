<?php

declare(strict_types=1);

namespace PabloJoan\Feature\Bucketing;

use PabloJoan\Feature\Value\User as UserValue;
use PabloJoan\Feature\Bucketing\Calculator\Id as Calculator;

class User implements Type
{
    function id (UserValue $user) : string
    {
        return $user->id();
    }

    function number (string $idToHash) : float
    {
        return (new Calculator)->number($idToHash);
    }
}
