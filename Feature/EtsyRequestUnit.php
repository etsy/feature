<?php

/**
 * A wrapper implementing our default experimental unit. Wraps the
 * global context of a web request with a bunch of Etsy-specific foo.
 */
class Feature_EtsyRequestUnit implements Feature_ExperimentalUnit {

    const USER   = 'user';
    const UAID   = 'uaid';
    const RANDOM = 'random';

    /*
     * Return the specially assigned variant, if any, due to any of
     * the clauses in the config stanza or false otherwise.
     */
    public function assignedVariant($data, $config) {
        // Ignore $data. Should be null. (Or assert that it is null.)
        $userID = $this->userID();

        return
            $this->variantFromURL($config) ?:
            $this->variantForUser($userID, $this->parseUsersOrGroups($config, 'users')) ?:
            $this->variantForGroup($userID, $this->parseUsersOrGroups($config, 'groups')) ?:
            $this->variantForAdmin($userID, $config->getVariantName('admin')) ?:
            $this->variantForInternal($config->getVariantName('internal'));
    }

    /*
     * Get the bucketing id for this experimental unit based on the
     * configured scheme. (I.e. the string value of the 'bucketing'
     * clause of the config stanza.)
     */
    public function bucketingID ($data, $scheme) {
        // Ignore $data. Should be null.
        switch ($scheme) {
        case self::UAID:
        case self::RANDOM:
            // In the RANDOM case we still need a bucketing id to keep
            // the assignment stable within a request.
            // Note that when being run from outside of a web request (e.g. crons),
            // there is no UAID, so we default to a static string
            return $this->uaid() ?: "no uaid";
        case self::USER:
            $userID = $this->userID();
            // Not clear if this is right. There's an argument to be
            // made that if we're bucketing by userID and the user is
            // not logged in we should treat the feature as disabled.
            return $userID;
        default:
            throw new InvalidArgumentException("Bad bucketing: $scheme");
        }
    }

    /*
     * What is our default bucketing scheme?
     */
    public function defaultBucketing() {
        return self::UAID;
    }


    private function parseUsersOrGroups($config, $what) {
        $stanza  = $config->stanza();
        $enabled = $config->enabled();

        $value = Feature_Util::arrayGet($stanza, $what);
        if (is_string($value) || is_numeric($value)) {
            // Users are configrued with their user names. Groups as
            // numeric ids. (Not sure if that's a great idea.)
          return array($value => self::ON);

        } elseif (self::isList($value)) {
            $result = array();
            foreach ($value as $who) {
              $result[strtolower($who)] = self::ON;
            }
            return $result;

        } elseif (is_array($value)) {
            $result = array();
            $bad_keys = is_array($enabled) ?
                array_keys(array_diff_key($value, $enabled)) :
                array();
            if (!$bad_keys) {
                foreach ($value as $variant => $whos) {
                    foreach (self::asArray($whos) as $who) {
                        $result[strtolower($who)] = $variant;
                    }
                }
                return $result;

            } else {
                $config->error("Unknown variants " . implode(', ', $bad_keys));
            }
        } else {
            return array();
        }
    }


    /*
     * For internal requests or if the feature has public_url_override
     * set to true, a specific variant can be specified in the
     * 'features' query parameter. In all other cases return false,
     * meaning nothing was specified. Note that foo:off will turn off
     * the 'foo' feature.
     */
    private function variantFromURL($config) {
        if ($config->getBoolean('public_url_override') or
            $this->isInternalRequest() or
            $this->isAdmin($userID)
        ) {
            return $config->variantFromURL('o');
        }
        return false;
    }

    /*
     * Get the variant this user should see, if one was configured,
     * false otherwise.
     */
    private function variantForUser ($userID, $users) {
        if ($userID && $users) {
            $name = $this->userName($userID);
            if ($name && array_key_exists($name, $users)) {
                return array($users[$name], 'u');
            }
        }
        return false;
    }

    /*
     * Get the variant this user should see based on their group
     * memberships, if one was configured, false otherwise. N.B. If
     * the user is in multiple groups that are configured to see
     * different variants, they'll get the variant for one of their
     * groups but there's no saying which one. If this is a problem in
     * practice we could make the configuration more complex. Or you
     * can just provide a specific variant via the 'users' property.
     */
    private function variantForGroup ($userID, $groups) {
        if ($userID) {
            foreach ($groups as $groupID => $variant) {
                if ($this->inGroup($userID, $groupID)) {
                    return array($variant, 'g');
                }
            }
        }
        return false;
    }

    /*
     * What variant, if any, should we return if the current user is
     * an admin.
     */
    private function variantForAdmin ($userID, $adminVariant) {
        if ($userID && $adminVariant) {
            if ($this->isAdmin($userID)) {
                return array($adminVariant, 'a');
            }
        }
        return false;
    }

    /*
     * What variant, if any, should we return for internal requests.
     */
    private function variantForInternal ($internalVariant) {
        if ($internalVariant) {
            if ($this->isInternalRequest()) {
                return array($internalVariant, 'i');
            }
        }
        return false;
    }

    private function uaid() {
        $uaid = UAIDCookie::getSecureCookie();
        return $uaid ? $uaid->get('uaid') : null;
    }

    private function userID () {
        return Std::loggedUser();
    }

    private function userName ($userID) {
        if (Etsy_ServerConfig::isAtlasRequest()) {
            return Atlas_Admin::getAuthUsername();
        } else {
            $user = EtsyORM::getFinder('User')->findRecord($userID);
            return $user ? strtolower($user->login_name) : null;
        }
    }

    private function inGroup ($userID, $groupID) {
        if (Etsy_ServerConfig::isAtlasRequest()) {
            // Atlas user IDs are taken from a different space
            return false;
        }
        return EtsyModel_GroupMembership::hasActiveMembership($userID, $groupID);
    }

    private function isAdmin ($userID) {
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

    private function isInternalRequest () {
        return HTTP_Request::getInstance()->isInternal();
    }

}
