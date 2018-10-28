<?php

declare(strict_types=1);

namespace PabloJoan\Feature\Value;

class User
{
    private $uaid;
    private $id;
    private $country;
    private $zipcode;
    private $region;
    private $isAdmin;
    private $internalIP;
    private $group;

    function __construct (array $user)
    {
        $this->uaid = $user['uaid'] ?? '';
        $this->id = $user['id'] ?? '';
        $this->group = $user['group'] ?? '';
        $this->zipcode = $user['zipcode'] ?? '';
        $this->region = $user['region'] ?? '';
        $this->country = $user['country'] ?? '';
        $this->isAdmin = $user['is-admin'] ?? false;
        $this->internalIP = $user['internal-ip'] ?? false;
    }

    function uaid () : string
    {
        return $this->uaid;
    }

    function id () : string
    {
        return $this->id;
    }

    function country () : string
    {
        return $this->country;
    }

    function zipcode () : string
    {
        return $this->zipcode;
    }

    function region () : string
    {
        return $this->region;
    }

    function isAdmin () : bool
    {
        return $this->isAdmin;
    }

    function internalIP () : bool
    {
        return $this->internalIP;
    }

    function group () : string
    {
        return $this->group;
    }
}
