<?php

namespace CafeMedia\Feature;

/**
 * A feature that can be enabled, disabled, ramped up, and A/B tested,
 * as well as enabled for certain classes of users. These objects
 * should not be accessed directly but rather through the API provided
 * by Feature.php which is more convenient and provides some caching.
 * 
 * Class Config
 * @package CafeMedia\Feature
 */
class Config
{
    /* Keys used in a feature configuration. */
    
    const DESCRIPTION         = 'description';
    
    const ENABLED             = 'enabled';
    
    const USERS               = 'users';
    
    const GROUPS              = 'groups';
    
    const UTM_SOURCES         = 'utm_sources';
    
    const ADMIN               = 'admin';
    
    const INTERNAL            = 'internal';
    
    const PUBLIC_URL_OVERRIDE = 'public_url_override';
    
    const BUCKETING           = 'bucketing';

    /* Special values for enabled property. */
    
    const ON  = 'on';  /* Feature is fully enabled. */
    
    const OFF = 'off'; /* Feature is fully disabled. */

    /* Bucketing schemes. */
    
    const UAID   = 'uaid';
    
    const USER   = 'user';
    
    const RANDOM = 'random';

    /**
     * @var
     */
    private $_name;
    /**
     * @var array
     */
    private $_cache;
    /**
     * @var World
     */
    private $_world;

    /**
     * @var mixed|null
     */
    private $_description;
    /**
     * @var array|int|mixed|null
     */
    private $_enabled;
    /**
     * @var array
     */
    private $_users;
    /**
     * @var array
     */
    private $_groups;
    /**
     * @var array
     */
    private $_utm_sources;
    /**
     * @var bool|mixed|null
     */
    private $_adminVariant;
    /**
     * @var bool|mixed|null
     */
    private $_internalVariant;
    /**
     * @var mixed|null
     */
    private $_public_url_override;
    /**
     * @var mixed|null
     */
    private $_bucketing;

    /**
     * @var array
     */
    private $_percentages;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * Construct a Config object from its config stanza.
     *
     * Config constructor.
     * @param $name
     * @param $stanza
     * @param World $world
     * @param Logger $logger
     */
    public function __construct($name, $stanza, World $world, Logger $logger)
    {
        $this->_name  = $name;
        $this->_cache = array();
        $this->_world = $world;
        $this->logger = $logger;

        // Special case to save some memory--if the value is just a
        // string that is the same as setting enabled to that variant
        // (typically 'on' or 'off' but possibly another variant
        // name). This reduces the number of array objects we have to
        // create when reading the config file.
        if (is_null($stanza)) {
            $stanza = array(self::ENABLED => self::OFF);
        }
        elseif (is_string($stanza)) {
            $stanza = array(self::ENABLED => $stanza);
        }

        // Pull stuff from the config stanza.
        $this->_description         = $this->parseDescription($stanza);
        $this->_enabled             = $this->parseEnabled($stanza);
        $this->_users               = $this->parseUsersOrGroups($stanza, self::USERS);
        $this->_groups              = $this->parseUsersOrGroups($stanza, self::GROUPS);
        $this->_utm_sources         = $this->parseUsersOrGroups($stanza, self::UTM_SOURCES);
        $this->_adminVariant        = $this->parseVariantName($stanza, self::ADMIN);
        $this->_internalVariant     = $this->parseVariantName($stanza, self::INTERNAL);
        $this->_public_url_override = $this->parsePublicURLOverride($stanza);
        $this->_bucketing           = $this->parseBucketBy($stanza);

        // Put the _enabled value into a more useful form for actually doing bucketing.
        $this->_percentages = $this->computePercentages();
    }

    ////////////////////////////////////////////////////////////////////////
    // Public API, though note that Feature.php is the only code that
    // should be using this class directly.

    /**
     * Is this feature enabled for the default id and the logged in
     * user, if any?
     *
     * @return bool
     */
    public function isEnabled ()
    {
        return $this->chooseVariant($this->bucketingID(), $this->_world->userID(), false) !== self::OFF;
    }

    /**
     * What variant is enabled for the default id and the logged in
     * user, if any?
     *
     * @return mixed|string
     */
    public function variant ()
    {
        return $this->chooseVariant($this->bucketingID(), $this->_world->userID(), true);
    }

    /**
     * Is this feature enabled for the given user?
     *
     * @param $user
     * @return bool
     */
    public function isEnabledFor ($user)
    {
        $userID = $this->getUserIdFrom($user);
        return $this->chooseVariant($userID, $userID, false) !== self::OFF;
    }

    /**
     * Is this feature enabled, bucketing on the given bucketing
     * ID? (Other methods of enabling a feature and specifying a
     * variant such as users, groups, and query parameters, will still
     * work.)
     *
     * @param $bucketingID
     * @return bool
     */
    public function isEnabledBucketingBy ($bucketingID)
    {
        return $this->chooseVariant($bucketingID, $this->_world->userID(), false) !== self::OFF;
    }

    /**
     * What variant is enabled for the given user?
     *
     * @param $user
     * @return mixed|string
     */
    public function variantFor ($user)
    {
        $userID = $this->getUserIdFrom($user);
        return $this->chooseVariant($userID, $userID, true);
    }

    /**
     * What variant is enabled, bucketing on the given bucketing ID,
     * if any?
     *
     * @param $bucketingID
     * @return mixed|string
     */
    public function variantBucketingBy ($bucketingID)
    {
        return $this->chooseVariant($bucketingID, $this->_world->userID(), true);
    }

    /**
     * Description of the feature.
     *
     * @return mixed|null
     */
    public function description ()
    {
        return $this->_description;
    }


    ////////////////////////////////////////////////////////////////////////
    // Internals

    /**
     * Accept different user objects and return user_id
     *
     * @param $user
     * @return mixed
     */
    private function getUserIdFrom($user)
    {
        return $user->user_id;
    }

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
     *
     * @param $userID - the identity of the user to be used for the
     * special 'admin', 'users', and 'groups' access checks.
     *
     * @param $inVariantMethod - were we called from variant or
     * variantFor, in which case we want to perform some certain
     * sanity checks to make sure the code is being used correctly.
     *
     * @return array|int|mixed|null|string
     */
    private function chooseVariant ($bucketingID, $userID, $inVariantMethod)
    {
        if ($inVariantMethod && $this->_enabled === self::ON) {
            $this->error('Variant check when fully enabled');
        }

        if (is_string($this->_enabled)) {
            // When enabled is on, off, or a variant name, that's the
            // end of the story.
            return $this->_enabled;
        }

        if (is_null($bucketingID)) {
            throw new \InvalidArgumentException(
                'no bucketing ID supplied. if testing, configure feature ' .
                "with enabled => 'on' or 'off', feature name = $this->_name"
            );
        }

        $bucketingID = (string)$bucketingID;
        if (isset($this->_cache[$bucketingID])) {
            // Note that this caching is not just an optimization:
            // it prevents us from double logging a single
            // feature--we only want to log each distinct checked
            // feature once.
            //
            // The caching also affects the semantics when we use
            // random bucketing (rather than hashing the id), i.e.
            // 'random' => 'true', by making the variant and
            // enabled status stable within a request.
            return $this->_cache[$bucketingID];
        }

        if ($_v = $this->variantFromURL($userID))  {}
        elseif ($_v = $this->variantForUser($userID)) {}
        elseif ($_v = $this->variantForViewingGroup($userID)) {}
        elseif ($_v = $this->variantForUtmSource($userID)) {}
        elseif ($_v = $this->variantForAdmin($userID)) {}
        elseif ($_v = $this->variantByPercentage($bucketingID)) {}
        else {
            $_v = array(self::OFF, 'w');
        }

        list($v, $selector) = $_v;

        if ($inVariantMethod && $v === self::OFF) {
            $this->error('Variant check outside enabled check');
        }
        $this->_world->log($this->_name, $v, $selector);

        return $this->_cache[$bucketingID] = $v;
    }

    /**
     * Return the globally accessible ID used by the one-arg isEnabled
     * and variant methods based on the feature's bucketing property.
     *
     * @return null|string
     */
    private function bucketingID ()
    {
        switch ($this->_bucketing) {
            case self::UAID:
            case self::RANDOM:
                // In the RANDOM case we still need a bucketing id to keep
                // the assignment stable within a request.
                // Note that when being run from outside of a web request (e.g. crons),
                // there is no UAID, so we default to a static string
                $uaid = $this->_world->uaid();
                return $uaid ? $uaid : 'no uaid';
            case self::USER:
                $userID = $this->_world->userID();
                // Not clear if this is right. There's an argument to be
                // made that if we're bucketing by userID and the user is
                // not logged in we should treat the feature as disabled.
                return !is_null($userID) ? $userID : $this->_world->uaid();
            default:
                throw new \InvalidArgumentException("Bad bucketing: $this->_bucketing");
        }
    }

    /**
     * For internal requests or if the feature has public_url_override
     * set to true, a specific variant can be specified in the
     * 'features' query parameter. In all other cases return false,
     * meaning nothing was specified. Note that foo:off will turn off
     * the 'foo' feature.
     *
     * @param $userID
     * @return array|bool|void
     */
    private function variantFromURL ($userID)
    {
        if (!$this->_public_url_override && !$this->_world->isInternalRequest() && !$this->_world->isAdmin($userID)) {
            return false;
        }

        $urlFeatures = $this->_world->urlFeatures();
        if (!$urlFeatures) {
            return false;
        }

        foreach (explode(',', $urlFeatures) as $f) {
            $parts = explode(':', $f);
            if ($parts[0] === $this->_name) {
                return array(isset($parts[1]) ? $parts[1] : self::ON, 'o');
            }
        }

        return false;
    }

    /**
     * Get the variant this user should see, if one was configured,
     * false otherwise.
     *
     * @param $userID
     * @return array|bool
     */
    private function variantForUser ($userID)
    {
        if (!$this->_users) {
            return false;
        }

        $name = $this->_world->userName($userID);
        if ($name && isset($this->_users[strtolower($name)])) {
            return array($this->_users[strtolower($name)], 'u');
        }

        return false;
    }

    /**
     * Get the variant visitor should see based on group
     * they're currently viewing
     *
     * @param $userID
     * @return array|bool
     */
    private function variantForViewingGroup ($userID = null)
    {
        foreach ($this->_groups as $groupID => $variant) {
            if ($this->_world->viewingGroup($groupID)) {
                return array($variant, 'g');
            }
        }
        return false;
    }

    /**
     * Get the variant visitor should see based on group
     * they're currently viewing
     *
     * @param $userID
     * @return array|bool
     */
    private function variantForUtmSource ($userID = null)
    {
        foreach ($this->_utm_sources as $utm_source => $variant) {
            if ($this->_world->isSource($utm_source)) {
                return array($variant, 's');
            }
        }
        return false;
    }

    /**
     * Get the variant this user should see based on their group
     * memberships, if one was configured, false otherwise. N.B. If
     * the user is in multiple groups that are configured to see
     * different variants, they'll get the variant for one of their
     * groups but there's no saying which one. If this is a problem in
     * practice we could make the configuration more complex. Or you
     * can just provide a specific variant via the 'users' property.
     *
     * @param $userID
     * @return array|bool
     */
    private function variantForGroup ($userID)
    {
        if (!$userID) {
            return false;
        }

        foreach ($this->_groups as $groupID => $variant) {
            if ($this->_world->inGroup($userID, $groupID)) {
                return array($variant, 'g');
            }
        }

        return false;
    }

    /**
     * What variant, if any, should we return if the current user is
     * an admin.
     *
     * @param $userID
     * @return array|bool
     */
    private function variantForAdmin ($userID)
    {
        if ($userID && $this->_adminVariant && $this->_world->isAdmin($userID)) {
            return array($this->_adminVariant, 'a');
        }
        return false;
    }

    /**
     * What variant, if any, should we return for internal requests.
     *
     * @return array|bool
     */
    private function variantForInternal ()
    {
        if ($this->_internalVariant && $this->_world->isInternalRequest()) {
            return array($this->_internalVariant, 'i');
        }
        return false;
    }

    /**
     * Finally, the normal case: use the percentage of users who
     * should see each variant to map a randomish number to a
     * particular variant.
     *
     * @param $id
     * @return array|bool
     */
    private function variantByPercentage ($id)
    {
        $n = 100 * $this->randomish($id);
        foreach ($this->_percentages as $v) {
            // === 100 check may not be necessary but I'm not good
            // enough numerical analyst to be sure.
            if ($n < $v[0] || $v[0] === 100) {
                return array($v[1], 'w');
            }
        }
        return false;
    }

    /**
     * A randomish number in [0, 1) based on the feature name and $id
     * unless we are bucketing completely at random
     *
     * @param $id
     * @return float|int
     */
    private function randomish ($id)
    {
        if ($this->_bucketing === self::RANDOM) {
            return $this->_world->random();
        }
        return $this->_world->hash("$this->_name-$id");
    }

    ////////////////////////////////////////////////////////////////////////
    // Configuration parsing

    /**
     * @param $stanza
     * @return mixed|null
     */
    private function parseDescription ($stanza)
    {
        return Util::arrayGet($stanza, self::DESCRIPTION, 'No description.');
    }

    /**
     * Parse the 'enabled' property of the feature's config stanza.
     *
     * @param $stanza
     * @return array|int|mixed|null
     */
    private function parseEnabled ($stanza)
    {
        $enabled = Util::arrayGet($stanza, self::ENABLED, 0);

        if (is_numeric($enabled)) {
            if ($enabled < 0) {
                $this->error("enabled ($enabled) < 0");
                $enabled = 0;
            }
            elseif ($enabled > 100) {
                $this->error("enabled ($enabled) > 100");
                $enabled = 100;
            }
            return array('on' => $enabled);

        }
        if (is_string($enabled) or is_array($enabled)) {
            return $enabled;
        }

        $this->error('Malformed enabled property');
        return false;
    }

    /**
     * Returns an array of pairs with the first element of the pair
     * being the upper-boundary of the variants percentage and the
     * second element being the name of the variant.
     *
     * @return array
     */
    private function computePercentages ()
    {
        $total = 0;
        $percentages = array();
        if (!is_array($this->_enabled)) {
            return $percentages;
        }

        foreach ($this->_enabled as $variant => $percentage) {
            if (!is_numeric($percentage) || $percentage < 0 || $percentage > 100) {
                $this->error("Bad percentage $percentage");
            }
            if ($percentage > 0) {
                $total += $percentage;
                $percentages[] = array($total, $variant);
            }
            if ($total > 100) {
                $this->error("Total of percentages > 100: $total");
            }
        }

        return $percentages;
    }

    /**
     * Parse the value of the 'users' and 'groups' properties of the
     * feature's config stanza, returning an array mappinng the user
     * or group names to they variant they should see.
     *
     * @param $stanza
     * @param $what
     * @return array
     */
    private function parseUsersOrGroups ($stanza, $what)
    {
        $value = Util::arrayGet($stanza, $what);
        if (is_string($value) || is_numeric($value)) {
            // Users are configrued with their user names. Groups as
            // numeric ids. (Not sure if that's a great idea.)
          return array($value => self::ON);

        }

        $result = array();

        if (self::isList($value)) {
            foreach ($value as $who) {
              $result[strtolower($who)] = self::ON;
            }
            return $result;
        }

        if (!is_array($value)) {
            return $result;
        }

        $bad_keys = is_array($this->_enabled) ? array_keys(array_diff_key($value, $this->_enabled)) : false;
        if ($bad_keys) {
            $this->error("Unknown variants " . implode(', ', $bad_keys));
            return $result;
        }

        foreach ($value as $variant => $whos) {
            foreach (self::asArray($whos) as $who) {
                $result[strtolower($who)] = $variant;
            }
        }

        return $result;
    }

    /**
     * Parse the variant name value for the 'admin' and 'internal'
     * properties. If non-falsy, must be one of the keys in the
     * enabled map unless enabled is 'on' or 'off'.
     *
     * @param $stanza
     * @param $what
     * @return bool|mixed|null
     */
    private function parseVariantName ($stanza, $what)
    {
        $value = Util::arrayGet($stanza, $what);
        if (!$value) {
            return false;
        }

        if (is_array($this->_enabled) && !isset($this->_enabled[$value])) {
            $this->error("Unknown variant $value");
        }

        return $value;
    }

    /**
     * @param $stanza
     * @return mixed|null
     */
    private function parsePublicURLOverride ($stanza)
    {
        return Util::arrayGet($stanza, self::PUBLIC_URL_OVERRIDE, false);
    }

    /**
     * @param $stanza
     * @return mixed|null
     */
    private function parseBucketBy ($stanza)
    {
        return Util::arrayGet($stanza, self::BUCKETING, self::UAID);
    }

    ////////////////////////////////////////////////////////////////////////
    // Genericish utilities

    /**
     * Is the given object an array value that could have been created
     * with array(...) with no =>'s in the ...?
     *
     * @param $a
     * @return bool
     */
    private static function isList($a)
    {
        return is_array($a) and array_keys($a) === range(0, count($a) - 1);
    }

    /**
     * @param $x
     * @return array
     */
    private static function asArray ($x)
    {
        return is_array($x) ? $x : array($x);
    }

    /**
     * @param $message
     */
    private function error ($message)
    {
        $this->logger->error("$message: feature $this->_name");
    }
}
