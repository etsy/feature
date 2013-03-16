<?php

/**
 * The interface Feature_Config needs to the outside world. This class
 * is used in the normal case but tests can use a mock
 * version. There's a reasonable argument that the code in Logger
 * should just be moved into this class since there's a fair bit of
 * passing stuff back and forth between here and Logger and Logger has
 * no useful independent existence.
 */
class Feature_World {

    private $_logger;
    private $_selections = array();

    public function __construct ($logger) {
        $this->_logger = $logger;
    }

    /*
     * Get the config value for the given key. For the moment we
     * always look under the new_config section of the config
     * file. Eventually (once we've excised all the old-style configs)
     * we'll move them up and take out the new_config prefix.
     */
    public function configValue($name, $default = null) {
        return Etsy_ServerConfig::getInstance()->getFeatureValue("new_config.$name", $default);
    }

    /**
     * UAID of the current request.
     */
    public function uaid() {
        $uaid = UAIDCookie::getSecureCookie();
        return $uaid ? $uaid->get('uaid') : null;
    }

    /**
     * User ID of the currently logged in user or null. Doesn't need
     * the ORM.
     */
    public function userID () {
        return Std::loggedUser();
    }

    /**
     * Login name of the currently logged in user or null. Needs the
     * ORM. If we're running as part of an Atlas request we ignore the
     * passed in userID and return instead the Atlas user name.
     */
    public function userName ($userID) {
        if (Etsy_ServerConfig::isAtlasRequest()) {
            return Atlas_Admin::getAuthUsername();
        } else {
            $user = EtsyORM::getFinder('User')->findRecord($userID);
            return $user ? strtolower($user->login_name) : null;
        }
    }

    /**
     * Is the given user a member of the given group? (This currently,
     * like the old config system, uses numeric group IDs in the
     * config file, in order to speed up the lookup--the numeric ID is
     * the primary key and we save having to look up the group by
     * name.)
     */
    public function inGroup ($userID, $groupID) {
        if (Etsy_ServerConfig::isAtlasRequest()) {
            // Atlas user IDs are taken from a different space
            return false;
        }
        return EtsyModel_GroupMembership::hasActiveMembership($userID, $groupID);
    }

    /**
     * Is the current user an admin?
     *
     * @param $userID the id of the relevant user, either the
     * currently logged in user or some other user.
     */
    public function isAdmin ($userID) {
        // If the UAID cookie belongs to the relevant user we can
        // check whether they are an admin without involving the
        // ORM. In cases where we are checking for a different user we
        // will end up using the ORM. There is, it seems, an edge case
        // where a user who logs out and back in as a different user
        // doesn't get a new UAID cookie so user id stored in the
        // cookie won't match the user id passed in which came from
        // Std::loggedUser(). (My understanding is that this is on
        // purpose so that we can detect the same person (or browser,
        // anyway) logging in as lots of different users.)
        //
        // In that case we return false so that we can guarantee that
        // code inside the ORM can safely use feature checks and
        // enable those features for admin as long. (Note, however,
        // that it can only use isEnabled/variant and not
        // isEnabledFor/variantFor.)

        if ($userID) {
            $uaid = UAIDCookie::getSecureCookie();
            if (
                $uaid instanceof SecureCookie && //could be null if not previously initialised
                $userID == Std::loggedUser() && //comes from a global, could differ from cookie-sourced value
                !is_null($uaid->get(UAIDCookie::USER_ID_ATTRIBUTE)) //check there's a user_id to compare against
            ) {
                return $uaid->get(UAIDCookie::USER_ID_ATTRIBUTE) == $userID &&
                    $uaid->get(UAIDCookie::ADMIN_ATTRIBUTE) == '1';
            } else if ($user = EtsyORM::getFinder('User')->findRecord($userID)) {
                return $user->isAdmin() || $user->isBoardMember();
            }
        }

        return false;
    }

    /**
     * Is this an internal request?
     */
    public function isInternalRequest () {
        return HTTP_Request::getInstance()->isInternal();
    }

    /*
     * 'features' query param for url overrides.
     */
    public function urlFeatures () {
        return array_key_exists('features', $_GET) ? $_GET['features'] : '';
    }

    /*
     * Produce a random number in [0, 1) for RANDOM bucketing.
     */
    public function random () {
        return mt_rand(0, mt_getrandmax() - 1) / mt_getrandmax();
    }

    /*
     * Produce a randomish number in [0, 1) based on the given id.
     */
    public function hash ($id) {
        return self::mapHex(hash('sha256', $id));
    }

    /*
     * Record that $variant has been selected for feature named $name
     * by $selector and pass the same information along to the logger.
     */
    public function log ($name, $variant, $selector) {
        $this->_selections[] = array($name, $variant, $selector);
        $this->_logger->log($name, $variant, $selector);
    }

    /*
     * Get the list of selections that we have recorded. The public
     * API for getting at the selections is Feature::selections which
     * should be the only caller of this method.
     */
    public function selections () {
        return $this->_selections;
    }

    /**
     * Map a hex value to the half-open interval [0, 1) while
     * preserving uniformity of the input distribution.
     *
     * @param string $hex a hex string
     * @return float
     */
    private static function mapHex($hex) {
        $len = min(40, strlen($hex));
        $vMax = 1 << $len;
        $v = 0;
        for ($i = 0; $i < $len; $i++) {
            $bit = hexdec($hex[$i]) < 8 ? 0 : 1;
            $v = ($v << 1) + $bit;
        }
        $w = $v / $vMax;
        return $w;
    }
}
