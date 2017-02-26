<?php

namespace CafeMedia\Feature;

/**
 * Construct a Config object from its config stanza.
 *
 * A feature that can be enabled, disabled, ramped up, and A/B tested,
 * as well as enabled for certain classes of users. These objects
 * should not be accessed directly but rather through the API provided
 * by Feature.php which is more convenient and provides some caching.
 */
class Config
{
    private $cache = [];
    private $world;
    private $stanza;
    private $name = '';

    public function __construct(World $world)
    {
        $this->world = $world;
    }

    public function addName($name)
    {
        $this->name = $name;
        $this->stanza = new Stanza($this->world->configValue($name));
        return $this;
    }

    ////////////////////////////////////////////////////////////////////////
    // Public API, though note that Feature.php is the only code that
    // should be using this class directly.

    /**
     * Is this feature enabled for the default id and the logged i user, if any?
     */
    public function isEnabled()
    {
        return $this->chooseVariant($this->bucketingID()) !== 'off';
    }

    /**
     * What variant is enabled for the default id and the logged in
     * user, if any?
     */
    public function variant()
    {
        return $this->chooseVariant($this->bucketingID());
    }

    /**
     * Is this feature enabled for the given user?
     */
    public function isEnabledFor(User $user)
    {
        return $this->chooseVariant($user->id) === 'on';
    }

    /**
     * Is this feature enabled, bucketing on the given bucketing
     * ID? (Other methods of enabling a feature and specifying a
     * variant such as users, groups, and query parameters, will still
     * work.)
     */
    public function isEnabledBucketingBy($bucketingID)
    {
        return $this->chooseVariant($bucketingID) !== 'off';
    }

    /**
     * What variant is enabled for the given user?
     */
    public function variantFor(User $user)
    {
        return $this->chooseVariant($user->id);
    }

    /**
     * What variant is enabled, bucketing on the given bucketing ID, if any?
     */
    public function variantBucketingBy($bucketingID)
    {
        return $this->chooseVariant($bucketingID);
    }

    /**
     * Description of the feature.
     */
    public function description()
    {
        return $this->stanza->description;
    }

    ////////////////////////////////////////////////////////////////////////
    // Internals

    /**
     * Get the name of the variant we should use. Returns OFF if the
     * feature is not enabled for $id. When $inVariantMethod is
     * true will also check the conditions that should hold for a
     * correct call to variant or variantFor: they should not be
     * called for features that are completely enabled (i.e. 'enabled'
     * => 'on') since all such variant-specific code should have been
     * cleaned up before changing the config and they should not be
     * called if the feature is, in fact, disabled for the given id
     * since those two methods should always be guarded by an
     * isEnabled/isEnabledFor call.
     *
     * @param $bucketingID - the id used to assign a variant based on
     * the percentage of users that should see different variants.
     */
    private function chooseVariant($bucketingID)
    {
        if (!$bucketingID) {
            throw new \InvalidArgumentException('no bucketing ID supplied.');
        }

        $bucketingID = (string)$bucketingID;
        if (isset($this->cache[$bucketingID])) {
            return $this->cache[$bucketingID];
        }

        return $this->cache[$bucketingID] = (string)(new Variant($this->world))
                                                 ->addStanza($this->stanza)
                                                 ->addBucketingID($bucketingID)
                                                 ->addName($this->name);
    }

    /**
     * Return the globally accessible ID used by the one-arg isEnabled
     * and variant methods based on the feature's bucketing property.
     */
    private function bucketingID()
    {
        if ($this->stanza->bucketing === 'random' ||
            $this->stanza->bucketing === 'uaid'
        ) {
            // In the RANDOM case we still need a bucketing id to keep
            // the assignment stable within a request.
            // Note that when being run from outside of a web request
            // (e.g. crons),
            // there is no UAID, so we default to a static string
            $uaid = $this->world->uaid();
            return $uaid ? $uaid : 'no uaid';
        }
        if ($this->stanza->bucketing === 'user') {
            $userID = $this->world->userID();
            // Not clear if this is right. There's an argument to be
            // made that if we're bucketing by userID and the user is
            // not logged in we should treat the feature as disabled.
            return $userID ? $userID : $this->world->uaid();
        }
        throw new \InvalidArgumentException(
            "Bad bucketing: {$this->stanza->bucketing}"
        );
    }
}
