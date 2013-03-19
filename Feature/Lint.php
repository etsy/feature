<?php

/**
 * Perform some checks on the Feature part of a config file. At the
 * moment performs only true syntax checks: it finds things that are
 * meaningless to the real parsing code and flags them. The only
 * exception is the check for old-style configuration which shows up
 * as an enabled value of 'rampup' accompanied by a 'rampup' clause.
 *
 * Could possibly be extended to detect various violations of tidyness
 * such as having users and groups configured for a config with a
 * string 'enabled' or even 'enabled' => 100.
 */
class Feature_Lint {

    private $_checked;
    private $_errors;
    private $_path;

    public function __construct() {
        $this->_checked = 0;
        $this->_errors  = array();
        $this->_path    = array();
        $this->syntax_keys = array(
            Feature_Config::ENABLED,
            Feature_Config::USERS,
            Feature_Config::GROUPS,
            Feature_Config::ADMIN,
            Feature_Config::INTERNAL,
            Feature_Config::PUBLIC_URL_OVERRIDE,
            Feature_Config::BUCKETING,
            'data',
        );

        $this->_legal_bucketing_values = array(
            Feature_Config::UAID,
            Feature_Config::USER,
            Feature_Config::RANDOM,
        );
    }

    public function run($file = null) {
        $config = $this->fromFile($file);
        $this->assert($config, "*** Bad configuration.");
        $this->lintNested($config);
    }

    public function checked() {
        return $this->_checked;
    }

    public function errors() {
        return $this->_errors;
    }

    private function fromFile($file) {
        global $server_config;
        $content = file_get_contents($file);
        error_reporting(0);
        $r = eval('?>' . $content);
        error_reporting(-1);
        if ($r === null) {
            return $server_config;
        } else if ($r === false) {
            return false;
        } else {
            Logger::error("Wut? $r");
            return false;
        }
    }

    /*
     * Recursively check nested feature configurations. Skips any keys
     * that have a syntactic meaning which includes 'data'.
     */
    private function lintNested($config) {
        foreach ($config as $name => $stanza) {
            if (!in_array($name, $this->syntax_keys)) {
                $this->lint($name, $stanza);
            }
        }
    }

    private function lint($name, $stanza) {
        array_push($this->_path, $name);
        $this->_checked += 1;
        if (is_array($stanza)) {
            $this->checkForOldstyle($stanza);
            $this->checkEnabled($stanza);
            $this->checkUsers($stanza);
            $this->checkGroups($stanza);
            $this->checkAdmin($stanza);
            $this->checkInternal($stanza);
            $this->checkPublicURLOverride($stanza);
            $this->checkBucketing($stanza);
            $this->lintNested($stanza);
        } else {
            $this->assert(is_string($stanza), "Bad stanza: $stanza.");
        }
        array_pop($this->_path);
    }

    private function assert($ok, $message) {
        if (!$ok) {
            $loc = "[" . implode('.', $this->_path) . "]";
            array_push($this->_errors, "$loc $message");
        }
    }

    private function checkForOldstyle($stanza) {
        $enabled = Feature_Util::arrayGet($stanza, Feature_Config::ENABLED, 0);
        $rampup  = Feature_Util::arrayGet($stanza, 'rampup', null);
        $this->assert($enabled !== 'rampup' || !$rampup, "Old-style config syntax detected.");
    }

    // 'enabled' must be a string, a number in [0,100], or an array of
    // (string => ints) such that the ints are all in [0,100] and the
    // total is <= 100.
    private function checkEnabled($stanza) {
        if (array_key_exists(Feature_Config::ENABLED, $stanza)) {
            $enabled = $stanza[Feature_Config::ENABLED];
            if (is_numeric($enabled)) {
                $this->assert($enabled >= 0, Feature_Config::ENABLED . " too small: $enabled");
                $this->assert($enabled <= 100, Feature_Config::ENABLED . "too big: $enabled");
            } else if (is_array($enabled)) {
                $tot = 0;
                foreach ($enabled as $k => $v) {
                    $this->assert(is_string($k), "Bad key $k in $enabled");
                    $this->assert(is_numeric($v), "Bad value $v for $k in $enabled");
                    $this->assert($v >= 0, "Bad value $v (too small) for $k");
                    $this->assert($v <= 100, "Bad value $v (too big) for $k");
                    if (is_numeric($v)) {
                        $tot += $v;
                    }
                }
                $this->assert($tot >= 0, "Bad total $tot (too small)");
                $this->assert($tot <= 100, "Bad total $tot (too big)");
            }
        }
    }

    private function checkUsers($stanza) {
        if (array_key_exists(Feature_Config::USERS, $stanza)) {
            $users = $stanza[Feature_Config::USERS];
            if (is_array($users) && !self::isList($users)) {
                foreach ($users as $variant => $value) {
                    $this->assert(is_string($variant), "User variant names must be strings.");
                    $this->checkUserValue($value);
                }
            } else {
                $this->checkUserValue($users);
            }
        }
    }

    private function checkUserValue($users) {
        $this->assert(is_string($users) || self::isList($users), Feature_Config::USERS . " must be string or list of strings: '$users'");
        if (self::isList($users)) {
            foreach ($users as $user) {
                $this->assert(is_string($user), Feature_Config::USERS . " elements must be strings: '$user'");
            }
        }
    }

    private function checkGroups($stanza) {
        if (array_key_exists(Feature_Config::GROUPS, $stanza)) {
            $groups = $stanza[Feature_Config::GROUPS];
            if (is_array($groups) && !self::isList($groups)) {
                foreach ($groups as $variant => $value) {
                    $this->assert(is_string($variant), "Group variant names must be strings.");
                    $this->checkGroupValue($value);
                }
            } else {
                $this->checkGroupValue($groups);
            }
        }
    }

    private function checkGroupValue($groups) {
        $this->assert(is_numeric($groups) || self::isList($groups), Feature_Config::GROUPS . " must be number or list of numbers");
        if (self::isList($groups)) {
            foreach ($groups as $group) {
                $this->assert(is_numeric($group), Feature_Config::GROUPS . " elements must be numbers: '$group'");
            }
        }
    }


    private function checkAdmin($stanza) {
        if (array_key_exists(Feature_Config::ADMIN, $stanza)) {
            $admin = $stanza[Feature_Config::ADMIN];
            $this->assert(is_string($admin), "Admin must be string naming variant: '$admin'");
        }
    }

    private function checkInternal($stanza) {
        if (array_key_exists(Feature_Config::INTERNAL, $stanza)) {
            $internal = $stanza[Feature_Config::INTERNAL];
            $this->assert(is_string($internal), "Internal must be string naming variant: '$internal'");
        }
    }

    private function checkPublicURLOverride($stanza) {
        if (array_key_exists(Feature_Config::PUBLIC_URL_OVERRIDE, $stanza)) {
            $public_url_override = $stanza[Feature_Config::PUBLIC_URL_OVERRIDE];
            $this->assert(is_bool($public_url_override), "public_url_override must be a boolean: '$public_url_override'");
            if (is_bool($public_url_override)) {
                $this->assert($public_url_override === true, "Gratuitous public_url_override (defaults to false)");
            }
        }
    }

    private function checkBucketing($stanza) {
        if (array_key_exists(Feature_Config::BUCKETING, $stanza)) {
            $bucketing = $stanza[Feature_Config::BUCKETING];
            $this->assert(is_string($bucketing), "Non-string bucketing: '$bucketing'");
            $this->assert(in_array($bucketing, $this->_legal_bucketing_values), "Illegal bucketing: '$bucketing'");
        }
    }

    private static function isList($a) {
        return is_array($a) and array_keys($a) === range(0, count($a) - 1);
    }
}
