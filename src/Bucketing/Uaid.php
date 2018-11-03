<?php

declare(strict_types=1);

namespace PabloJoan\Feature\Bucketing;

use PabloJoan\Feature\Value\User;
use PabloJoan\Feature\Bucketing\Calculator\Id as Calculator;

class Uaid implements Type
{
    function id (User $user) : string
    {
        return $user->uaid();
    }

    function number (string $idToHash) : float
    {
        return (new Calculator)->number($idToHash);
    }
}
