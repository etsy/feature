<?php

/**
 * A feature that can be enabled, disabled, ramped up, and A/B tested,
 * as well as enabled for certain classes of users. These objects
 * should not be accessed directly but rather through the API provided
 * by Feature.php which is more convenient and provides some caching.
 */
class Feature_Config {

    /* Standard keys used in a feature configuration. */
    const ENABLED   = 'enabled';
    const UNIT      = 'unit';
    const BUCKETING = 'bucketing';

    /* Special values for enabled property. */
    const ON  = 'on';  /* Feature is fully enabled. */
    const OFF = 'off'; /* Feature is fully disabled. */

    /* A special bucketing name. */
    const RANDOM = 'random';

    private $_name;
    private $_cache;
    private $_world;
    private $_stanza;

    private $_enabled;
    private $_bucketing;
    private $_percentages;

    /*
     * Construct a Config object from its config stanza.
     */
    public function __construct($name, $stanza, $world) {
        $this->_name   = $name;
        $this->_cache  = array();
        $this->_world  = $world;

        if (is_null($stanza)) {
            // Missing stanza is the same as off.
            $this->_stanza = array(self::ENABLED => self::OFF);
        } elseif (is_string($stanza)) {
            // A special case to save some memory in the config
            // array--if the value is just a string that is the same
            // as setting enabled to that variant (typically 'on' or
            // 'off' but possibly another variant name). This reduces
            // the number of array objects we have to create when
            // reading the config file.
            $this->_stanza = array(self::ENABLED => $stanza);
        } else {
            // Normal case.
            $this->_stanza = $stanza;
        }

        // Get the 'enabled' value in two forms, one more useful form
        // for actually doing bucketing.
        $this->_enabled     = $this->parseEnabled();
        $this->_percentages = $this->computePercentages();

        // Get the type of experimental unit used for this feature and
        // use it to get the factory we will use to make experimental
        // unit objects. Yes, this makes World a factory factory. Oh
        // well. The unit factory knows the default bucketing scheme and how
        $unitName         = $this->getStringOrNull(self::UNIT);
        $this->_unit      = $this->_world->unit($unitName);
        $defaultBucketing = $this->_unit->defaultBucketing();
        $this->_bucketing = $this->getString(self::BUCKETING, $defaultBucketing);

    }


    ////////////////////////////////////////////////////////////////////////
    // Public API, though note that Feature.php is the only code that
    // should be using this class directly.

    /*
     * Is this feature enabled for the appropriate experimental unit?
     */
    public function isEnabled ($data) {
        return $this->chooseVariant($data, false) !== self::OFF;
    }

    /*
     * What variant is enabled for the appropriate experimental unit?
     */
    public function variant ($data) {
        return $this->chooseVariant($data, true);;
    }


    ////////////////////////////////////////////////////////////////////////
    // Internals

    /*
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
     * @param $bucketingID the id used to assign a variant based on
     * the percentage of users that should see different variants.
     *
     * @param $userID the identity of the user to be used for the
     * special 'admin', 'users', and 'groups' access checks.
     *
     * @param $inVariantMethod were we called from variant or
     * variantFor, in which case we want to perform some certain
     * sanity checks to make sure the code is being used correctly.
     */
    private function chooseVariant ($data, $inVariantMethod) {

        if ($inVariantMethod && $this->_enabled === self::ON) {
            $this->error("Variant check when fully enabled");
        }

        if (is_string($this->_enabled)) {
            // When enabled is 'on', 'off', or another variant name,
            // that's the end of the story.
            return $this->_enabled;
        } else {

            $bucketingID = $this->_unit->bucketingID($data, $this->_bucketing);

            if (!array_key_exists($bucketingID, $this->_cache)) {
                // Note that this caching is not just an optimization:
                // it prevents us from double logging a single
                // feature--we only want to log each distinct checked
                // feature once.
                //
                // The caching also affects the semantics when we use
                // random bucketing (rather than hashing the id), i.e.
                // 'random' => 'true', by making the variant and
                // enabled status stable within a request.
                list($v, $selector) =
                    $this->_unit->assignedVariant($data, $this) ?:
                    $this->variantByPercentage($bucketingID) ?:
                    array(self::OFF, 'w');

                if ($inVariantMethod && $v === self::OFF) {
                    $this->error("Variant check outside enabled check");
                }

                $this->_world->log($this->_name, $v, $selector);
                $this->_cache[$bucketingID] = $v;
            }

            return $this->_cache[$bucketingID];
        }
    }

    /*
     * The normal case where there is no explicitly assigned variant.
     */
    private function variantByPercentage($id) {
        // If the bucketing id is null (e.g. if we're bucketing by
        // user id and the user is not signed in) then we treat the
        // feature as OFF but with a different selector so it's not
        // counted as part of the experimental data.
        if (is_null($id)) {
            return array(self::OFF, 'x');
        }

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

    /*
     * A randomish number in [0, 1) based on the feature name and $id
     * unless we are bucketing completely at random.
     */
    private function randomish ($id) {
        return $this->_bucketing === self::RANDOM
            ? $this->_world->random() : $this->_world->hash($this->_name . '-' . $id);
    }

    ////////////////////////////////////////////////////////////////////////
    // Configuration parsing

    /*
     * Parse the 'enabled' property of the feature's config stanza.
     */
    private function parseEnabled() {

        $enabled = Feature_Util::arrayGet($this->_stanza, self::ENABLED, 0);

        if (is_numeric($enabled)) {
            if ($enabled < 0) {
                $this->error("enabled ($enabled) < 0");
                $enabled = 0;
            } elseif ($enabled > 100) {
                $this->error("enabled ($enabled) > 100");
                $enabled = 100;
            }
            return array('on' => $enabled);

        } elseif (is_string($enabled) or is_array($enabled)) {
            return $enabled;
        } else {
            $this->error("Malformed enabled property");
        }
    }

    /*
     * Returns an array of pairs with the first element of the pair
     * being the upper-boundary of the variants percentage and the
     * second element being the name of the variant.
     */
    private function computePercentages () {
        $total = 0;
        $percentages = array();
        if (is_array($this->_enabled)) {
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
        }
        return $percentages;
    }


    ////////////////////////////////////////////////////////////////////////
    // Various methods for parsing values out of the config stanza for
    // use by wrappers.

    public function stanza() {
        return $this->_stanza;
    }

    public function enabled() {
        return $this->_enabled;
    }
    /*
     * Get a value out of the config stanza that must be one of the
     * values in enabled, if the latter is an array.
     */
    public function getVariantName ($what) {
        $value = Feature_Util::arrayGet($this->_stanza, $what);
        if ($value) {
            if (is_array($this->_enabled)) {
                if (array_key_exists($value, $this->_enabled)) {
                    return $value;
                } else {
                    $this->error("Unknown variant $value");
                }
            } else {
                return $value;
            }
        } else {
            return false;
        }
    }

    public function getBoolean ($key, $default = false) {
        $value = Feature_Util::arrayGet($this->_stanza, $key, false);
        if (is_bool($value)) {
            return $value;
        } else {
            $this->error("$key value $value not boolean");
        }
    }

    public function getString ($key, $default = null) {
        $value = Feature_Util::arrayGet($this->_stanza, $key, $default);
        if (is_string($value)) {
            return $value;
        } else {
            $this->error("$key value $value not string");
        }
    }

    public function getStringOrNull ($key) {
        $value = Feature_Util::arrayGet($this->_stanza, $key);
        if (is_string($value) or is_null($value)) {
            return $value;
        } else {
            $this->error("$key value $value not string");
        }
    }

    /*
     * Compute the variant from the URL in the standard way. Up to
     * ExperimentalUnit classes to call this when appropriate. (E.g.
     * at Etsy we only allow URL overrides for internal requests
     * unless the feature is configured with 'public_url_override' =>
     * true.
     */
    public function variantFromURL($selector) {
        $urlFeatures = array_key_exists('features', $_GET) ? $_GET['features'] : '';
        if ($urlFeatures) {
            foreach (explode(',', $urlFeatures) as $f) {
                $parts = explode(':', $f);
                if ($parts[0] === $this->_name) {
                    return array(isset($parts[1]) ? $parts[1] : Feature_Config::ON, $selector);
                }
            }
        }
        return false;
    }


    ////////////////////////////////////////////////////////////////////////
    // Genericish utilities

    /*
     * Is the given object an array value that could have been created
     * with array(...) with no =>'s in the ...?
     */
    private static function isList($a) {
        return is_array($a) and array_keys($a) === range(0, count($a) - 1);
    }

    private static function asArray ($x) {
        return is_array($x) ? $x : array($x);
    }

    private function error ($message) {
        // IMPLEMENT FOR YOUR CONTEXT
    }
}
