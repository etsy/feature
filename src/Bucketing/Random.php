<?php

declare(strict_types=1);

namespace PabloJoan\Feature\Bucketing;

use PabloJoan\Feature\Value\User;
use PabloJoan\Feature\Bucketing\Calculator\Random as Calculator;

class Random implements Type
{
    function id (User $user) : string
    {
        return $user->uaid() ?: 'no uaid';
    }

    function number (string $idToHash) : float
    {
        return (new Calculator)->number();
    }
}
