<?php

declare(strict_types=1);

namespace PabloJoan\Feature\Bucketing;

use PabloJoan\Feature\Value\User;

interface Type
{
    function id (User $user) : string;

    /**
     * A random-ish number between 0 and 100 based on the feature name and $id
     * unless we are bucketing completely at random
     */
    function number (string $idToHash) : float;
}
