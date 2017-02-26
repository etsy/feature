<?php

namespace CafeMedia\Feature;

class User
{
    private $uaid = '';
    private $id = '';
    private $name = '';
    private $isAdmin = false;
    private $group = false;
    private $internalIP = false;
    private $zipcode = '';
    private $region = '';
    private $country = '';

    public function __construct(array $user)
    {
        if (!empty($user['user-uaid'])) $this->uaid = $user['user-uaid'];
        if (!empty($user['user-id'])) $this->id = $user['user-id'];
        if (!empty($user['user-name'])) $this->name = $user['user-name'];
        if (!empty($user['is-admin'])) $this->isAdmin = $user['is-admin'];
        if (!empty($user['user-group'])) $this->group = $user['user-group'];
        if (!empty($user['zipcode'])) $this->zipcode = $user['zipcode'];
        if (!empty($user['region'])) $this->region = $user['region'];
        if (!empty($user['country'])) $this->country = $user['country'];
        if (!empty($user['internal-ip'])) {
            $this->internalIP = $user['internal-ip'];
        }
    }

    public function __get($name)
    {
        if (isset($this->$name)) return $this->$name ? $this->$name : false;
        throw new \Exception("$name is not a property of the User class");
    }
}