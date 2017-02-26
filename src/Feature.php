<?php

namespace CafeMedia\Feature;

/**
 * The public API testing whether a specific feature is enabled and,
 * if so, what variant should be used.
 *
 * Primary public API:
 *
 *   Feature::isEnabled('foo');
 *   Feature::variant('foo');
 *
 * For cases when we want to bucket on a user other than the currently
 * logged in user (e.g. to bucket how we treat listings by their
 * owners) this secondary API is available:
 *
 *   Feature::isEnabledFor('foo', $user);
 *   Feature::variantFor('foo', $user);
 *
 * And for case when we want to bucket on something else entirely
 * (such as a shop ID), we provide these two methods:
 *
 *   Feature::isEnabledBucketingBy('foo', $bucketingID);
 *   Feature::variantBucketingBy('foo', $bucketingID);
 */
class Feature
{
    private $world;
    private $configCache = [];
    private $features = [];
    private $source = '';
    private $url = '';
    private $user;

    public function __construct(array $config)
    {
        $this->features = $config;
    }

    public function addUser(array $user)
    {
        $this->user = new User($user);
        return $this;
    }

    public function addSource($source)
    {
        $this->source = $source;
        return $this;
    }

    public function addUrl($url)
    {
        $this->source = $url;
        return $this;
    }

    /**
     * Test whether the named feature is enabled for the current user.
     */
    public function isEnabled($name)
    {
        return $this->fromConfig($name)->isEnabled();
    }

    /**
     * Test whether the named feature is enabled for a given
     * user. This method should only be used when we want to bucket
     * based on a user other than the current logged in user, e.g. if
     * we are bucketing different listings based on their owner.
     */
    public function isEnabledFor($name, array $user)
    {
        return $this->fromConfig($name)->isEnabledFor(new User($user));
    }

    /**
     * Test whether the named feature is enabled for a given
     * arbitrary string. This method should only be used when we want to bucket
     * based on something other than a user,
     * e.g. shops, teams, treasuries, tags, etc.
     */
    public function isEnabledBucketingBy($name, $string)
    {
        return $this->fromConfig($name)->isEnabledBucketingBy($string);
    }

    /**
     * Get the name of the A/B variant for the named feature for the
     * current user. Logs an error if called when isEnabled($name)
     * doesn't return true. (I.e. calls to this method should only
     * occur in blocks guarded by an isEnabled check.)
     *
     * Also logs an error if 'enabled' is 'on' for the named feature
     * since there should be no variant-dependent code left when a
     * feature has been fully enabled. To clean up a finished
     * experiment, first set 'enabled' to the name of the winning
     * variant.
     */
    public function variant($name)
    {
        return $this->fromConfig($name)->variant();
    }

    /**
     * Get the name of the A/B variant for the named feature for the
     * given user. This method should only be used when we want to
     * bucket based on a user other than the current logged in user,
     * e.g. if we are bucketing different listings based on their
     * owner.
     *
     * Logs an error if called when isEnabledFor($name, $user) doesn't
     * return true. (I.e. calls to this method should only occur in
     * blocks guarded by an isEnabledFor check.)
     * Also logs an error if 'enabled' is 'on' for the named feature
     * since there should be no variant-dependent code left when a
     * feature has been fully enabled. To clean up a finished
     * experiment, first set 'enabled' to the name of the winning
     * variant.
     */
    public function variantFor($name, array $user)
    {
        return $this->fromConfig($name)->variantFor(new User($user));
    }

    /**
     * Get the name of the A/B variant for the named feature,
     * bucketing by the given bucketing ID. (For other checks such as
     * admin, and user whitelists uses the current user which may or
     * may not make sense. If it doesn't make sense, don't configure
     * the feature to use those mechanisms.) Logs an error if called
     * when isEnabled($name) doesn't return true. (I.e. calls to this
     * method should only occur in blocks guarded by an isEnabled
     * check.)
     *
     * Also logs an error if 'enabled' is 'on' for the named feature
     * since there should be no variant-dependent code left when a
     * feature has been fully enabled. To clean up a finished
     * experiment, first set 'enabled' to the name of the winning
     * variant.
     */
    public function variantBucketingBy($name, $bucketingID)
    {
        return $this->fromConfig($name)->variantBucketingBy($bucketingID);
    }

    public function description($name)
    {
        return $this->fromConfig($name)->description();
    }

    /**
     * Get the named feature object. We cache the object after
     * building it from the config stanza to speed lookups.
     */
    private function fromConfig($name)
    {
        if (isset($this->configCache[$name])) return $this->configCache[$name];

        $this->configCache[$name] = (new Config($this->world()))->addName($name);
        return $this->configCache[$name];
    }

    /**
     * This API always uses the default World. Config takes
     * the world as an argument in order to ease unit testing.
     */
    private function world()
    {
        if ($this->world instanceof World) return $this->world;
        $this->world = (new World($this->features))->addUser($this->user)
                                                   ->addSource($this->source)
                                                   ->addUrl($this->url);
        unset($this->features, $this->user, $this->source, $this->url);
        return $this->world;
    }
}
