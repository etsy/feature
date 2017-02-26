<?php

namespace CafeMedia\Feature;

/**
 * The interface Config needs to the outside world. This class
 * is used in the normal case but tests can use a mock
 * version. There's a reasonable argument that the code in Logger
 * should just be moved into this class since there's a fair bit of
 * passing stuff back and forth between here and Logger and Logger has
 * no useful independent existence.
 */
class World
{
    private $features;
    private $source;
    private $url = '';
    private $user;

    public function __construct(array $features)
    {
        $this->features = $features;
    }

    public function addUser(User $user)
    {
        $this->user = $user;
        return $this;
    }

    public function addSource($source)
    {
        $this->source = $source;
        return $this;
    }

    public function addUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * Get the config value for the given key.
     */
    public function configValue($name)
    {
        if (empty($this->features[$name]) ||
            !is_array($this->features[$name])
        ) {
            throw new \Exception("no config available for feature $name");
        }
        return $this->features[$name];
    }

    /**
     * UAID of the current request.
     */
    public function uaid()
    {
        return $this->user->uaid;
    }

    /**
     * User ID of the currently logged in user or null.
     */
    public function userID()
    {
        return $this->user->id;
    }

    /**
     * Login name of the currently logged in user or null. Needs the
     * ORM. If we're running as part of an Atlas request we ignore the
     * passed in userID and return instead the Atlas user name.
     */
    public function userName()
    {
        return $this->user->name;
    }

    /**
     * zipcode of the currently logged in user.
     */
    public function zipcode()
    {
        return $this->user->zipcode;
    }

    /**
     * region of the currently logged in user.
     */
    public function region()
    {
        return $this->user->region;
    }

    /**
     * country of the currently logged in user.
     */
    public function country()
    {
        return $this->user->country;
    }

    /**
     * Is the visitor in a specific group?
     */
    public function viewingGroup($groupID)
    {
        return $this->user->group == $groupID;
    }

    /**
     * Is the visitor from a particular source?
     */
    public function isSource($source)
    {
        return $this->source == $source;
    }

    /**
     * Is the current user an admin?
     */
    public function isAdmin()
    {
        return $this->user->isAdmin;
    }

    /**
     * Is this an internal request?
     */
    public function isInternalRequest()
    {
        return $this->user->internalIP;
    }

    /**
     * 'features' query param for url overrides.
     */
    public function urlFeatures()
    {
        return !empty($this->url) ? $this->url : '';
    }
}
