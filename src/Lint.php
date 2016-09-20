<?php

namespace CafeMedia\Feature;

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
/**
 * Class Lint
 * @package CafeMedia\Feature
 */
class Lint {

    /**
     * @var int
     */
    private $_checked;
    /**
     * @var array
     */
    private $_errors;
    /**
     * @var array
     */
    private $_path;

    /**
     * Lint constructor.
     */
    public function __construct() {
        $this->_checked = 0;
        $this->_errors  = array();
        $this->_path    = array();
        $this->syntax_keys = array(
            Config::ENABLED,
            Config::USERS,
            Config::GROUPS,
            Config::ADMIN,
            Config::INTERNAL,
            Config::PUBLIC_URL_OVERRIDE,
            Config::BUCKETING,
            'data',
        );

        $this->_legal_bucketing_values = array(
            Config::UAID,
            Config::USER,
            Config::RANDOM,
        );
    }

    /**
     * @param null $file
     */
    public function run($file = null) {
        $config = $this->fromFile($file);
        $this->assert($config, "*** Bad configuration.");
        $this->lintNested($config);
    }

    /**
     * @return int
     */
    public function checked() {
        return $this->_checked;
    }

    /**
     * @return array
     */
    public function errors() {
        return $this->_errors;
    }

    /**
     * @param $file
     * @return bool
     */
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
            //Logger::error("Wut? $r");
            return false;
        }
    }

    /*
     * Recursively check nested feature configurations. Skips any keys
     * that have a syntactic meaning which includes 'data'.
     */
    /**
     * @param $config
     */
    private function lintNested($config) {
        foreach ($config as $name => $stanza) {
            if (!in_array($name, $this->syntax_keys)) {
                $this->lint($name, $stanza);
            }
        }
    }

    /**
     * @param $name
     * @param $stanza
     */
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

    /**
     * @param $ok
     * @param $message
     */
    private function assert($ok, $message) {
        if (!$ok) {
            $loc = "[" . implode('.', $this->_path) . "]";
            array_push($this->_errors, "$loc $message");
        }
    }

    /**
     * @param $stanza
     */
    private function checkForOldstyle($stanza) {
        $enabled = Util::arrayGet($stanza, Config::ENABLED, 0);
        $rampup  = Util::arrayGet($stanza, 'rampup', null);
        $this->assert($enabled !== 'rampup' || !$rampup, "Old-style config syntax detected.");
    }

    // 'enabled' must be a string, a number in [0,100], or an array of
    // (string => ints) such that the ints are all in [0,100] and the
    // total is <= 100.
    /**
     * @param $stanza
     */
    private function checkEnabled($stanza) {
        if (array_key_exists(Config::ENABLED, $stanza)) {
            $enabled = $stanza[Config::ENABLED];
            if (is_numeric($enabled)) {
                $this->assert($enabled >= 0, Config::ENABLED . " too small: $enabled");
                $this->assert($enabled <= 100, Config::ENABLED . "too big: $enabled");
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

    /**
     * @param $stanza
     */
    private function checkUsers($stanza) {
        if (array_key_exists(Config::USERS, $stanza)) {
            $users = $stanza[Config::USERS];
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

    /**
     * @param $users
     */
    private function checkUserValue($users) {
        $this->assert(is_string($users) || self::isList($users), Config::USERS . " must be string or list of strings: '$users'");
        if (self::isList($users)) {
            foreach ($users as $user) {
                $this->assert(is_string($user), Config::USERS . " elements must be strings: '$user'");
            }
        }
    }

    /**
     * @param $stanza
     */
    private function checkGroups($stanza) {
        if (array_key_exists(Config::GROUPS, $stanza)) {
            $groups = $stanza[Config::GROUPS];
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

    /**
     * @param $groups
     */
    private function checkGroupValue($groups) {
        $this->assert(is_numeric($groups) || self::isList($groups), Config::GROUPS . " must be number or list of numbers");
        if (self::isList($groups)) {
            foreach ($groups as $group) {
                $this->assert(is_numeric($group), Config::GROUPS . " elements must be numbers: '$group'");
            }
        }
    }


    /**
     * @param $stanza
     */
    private function checkAdmin($stanza) {
        if (array_key_exists(Config::ADMIN, $stanza)) {
            $admin = $stanza[Config::ADMIN];
            $this->assert(is_string($admin), "Admin must be string naming variant: '$admin'");
        }
    }

    /**
     * @param $stanza
     */
    private function checkInternal($stanza) {
        if (array_key_exists(Config::INTERNAL, $stanza)) {
            $internal = $stanza[Config::INTERNAL];
            $this->assert(is_string($internal), "Internal must be string naming variant: '$internal'");
        }
    }

    /**
     * @param $stanza
     */
    private function checkPublicURLOverride($stanza) {
        if (array_key_exists(Config::PUBLIC_URL_OVERRIDE, $stanza)) {
            $public_url_override = $stanza[Config::PUBLIC_URL_OVERRIDE];
            $this->assert(is_bool($public_url_override), "public_url_override must be a boolean: '$public_url_override'");
            if (is_bool($public_url_override)) {
                $this->assert($public_url_override === true, "Gratuitous public_url_override (defaults to false)");
            }
        }
    }

    /**
     * @param $stanza
     */
    private function checkBucketing($stanza) {
        if (array_key_exists(Config::BUCKETING, $stanza)) {
            $bucketing = $stanza[Config::BUCKETING];
            $this->assert(is_string($bucketing), "Non-string bucketing: '$bucketing'");
            $this->assert(in_array($bucketing, $this->_legal_bucketing_values), "Illegal bucketing: '$bucketing'");
        }
    }

    /**
     * @param $a
     * @return bool
     */
    private static function isList($a) {
        return is_array($a) and array_keys($a) === range(0, count($a) - 1);
    }
}
