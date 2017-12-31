<?php

declare(strict_types=1);

namespace PabloJoan\Feature\Contract;

interface User
{
    function __construct (array $user);

    function uaid () : string;

    function id () : string;

    function country () : string;

    function zipcode () : string;

    function region () : string;

    function isAdmin () : bool;

    function internalIP () : bool;

    function group () : string;
}
