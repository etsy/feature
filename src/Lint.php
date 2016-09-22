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
 *
 * Class Lint
 * @package CafeMedia\Feature
 */
class Lint
{
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
     * @var null
     */
    private $server_config;
    /**
     * @var Logger
     */
    private $logger;

    /**
     * Lint constructor.
     * @param null $server_config
     * @param Logger $logger
     */
    public function __construct($server_config = null, Logger $logger)
    {
        $this->server_config = $server_config;
        $this->logger = $logger;
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
            'data'
        );

        $this->_legal_bucketing_values = array(Config::UAID, Config::USER, Config::RANDOM);
    }

    /**
     * @param null $file
     */
    public function run($file = null)
    {
        $config = $this->fromFile($file);
        $this->assert($config, '*** Bad configuration.');
        $this->lintNested($config);
    }

    /**
     * @return int
     */
    public function checked()
    {
        return $this->_checked;
    }

    /**
     * @return array
     */
    public function errors()
    {
        return $this->_errors;
    }

    /**
     * @param $file
     * @return bool
     */
    private function fromFile($file)
    {
        error_reporting(0);
        $r = eval('?>' . file_get_contents($file));
        error_reporting(-1);

        if ($r === null) {
            return $this->server_config;
        }

        if ($r === false) {
            return false;
        }

        $this->logger->error("Wut? $r");
        return false;
    }

    /**
     * Recursively check nested feature configurations. Skips any keys
     * that have a syntactic meaning which includes 'data'.
     *
     * @param $config
     */
    private function lintNested($config)
    {
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
    private function lint($name, $stanza)
    {
        $this->_path[] = $name;
        ++$this->_checked;

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
        }
        else {
            $this->assert(is_string($stanza), "Bad stanza: $stanza.");
        }

        array_pop($this->_path);
    }

    /**
     * @param $ok
     * @param $message
     */
    private function assert($ok, $message)
    {
        if (!$ok) {
            $this->_errors[] = '[' . implode('.', $this->_path) . "] $message";
        }
    }

    /**
     * @param $stanza
     */
    private function checkForOldstyle($stanza)
    {
        $this->assert(Util::arrayGet(
            $stanza,
            Config::ENABLED, 0) !== 'rampup' || !Util::arrayGet($stanza, 'rampup', null),
            'Old-style config syntax detected.'
        );
    }

    /**
     * 'enabled' must be a string, a number in [0,100], or an array of
     * (string => ints) such that the ints are all in [0,100] and the
     * total is <= 100.
     *
     * @param $stanza
     */
    private function checkEnabled($stanza)
    {
        if (!isset($stanza[Config::ENABLED])) {
            return;
        }

        if (is_numeric($stanza[Config::ENABLED])) {
            $this->assert($stanza[Config::ENABLED] >= 0, Config::ENABLED . " too small: {$stanza[Config::ENABLED]}");
            $this->assert($stanza[Config::ENABLED] <= 100, Config::ENABLED . "too big: {$stanza[Config::ENABLED]}");
            return;
        }

        if (!is_array($stanza[Config::ENABLED])) {
            return;
        }

        $tot = 0;
        foreach ($stanza[Config::ENABLED] as $k => $v) {
            $this->assert(is_string($k), "Bad key $k in {$stanza[Config::ENABLED]}");
            $this->assert(is_numeric($v), "Bad value $v for $k in {$stanza[Config::ENABLED]}");
            $this->assert($v >= 0, "Bad value $v (too small) for $k");
            $this->assert($v <= 100, "Bad value $v (too big) for $k");
            if (is_numeric($v)) {
                $tot += $v;
            }
        }
        $this->assert($tot >= 0, "Bad total $tot (too small)");
        $this->assert($tot <= 100, "Bad total $tot (too big)");
    }

    /**
     * @param $stanza
     */
    private function checkUsers($stanza)
    {
        if (!isset($stanza[Config::USERS])) {
            return;
        }

        if (!is_array($stanza[Config::USERS]) || self::isList($stanza[Config::USERS])) {
            $this->checkUserValue($stanza[Config::USERS]);
            return;
        }

        foreach ($stanza[Config::USERS] as $variant => $value) {
            $this->assert(is_string($variant), 'User variant names must be strings.');
            $this->checkUserValue($value);
        }
    }

    /**
     * @param $users
     */
    private function checkUserValue($users)
    {
        $this->assert(
            is_string($users) || self::isList($users),
            Config::USERS . " must be string or list of strings: '$users'"
        );
        if (!self::isList($users)) {
            return;
        }

        foreach ($users as $user) {
            $this->assert(is_string($user), Config::USERS . " elements must be strings: '$user'");
        }
    }

    /**
     * @param $stanza
     */
    private function checkGroups($stanza)
    {
        if (!isset($stanza[Config::GROUPS])) {
            return;
        }

        if (!is_array($stanza[Config::GROUPS]) || self::isList($stanza[Config::GROUPS])) {
            $this->checkGroupValue($stanza[Config::GROUPS]);
            return;
        }

        foreach ($stanza[Config::GROUPS] as $variant => $value) {
            $this->assert(is_string($variant), 'Group variant names must be strings.');
            $this->checkGroupValue($value);
        }
    }

    /**
     * @param $groups
     */
    private function checkGroupValue($groups)
    {
        $this->assert(
            is_numeric($groups) || self::isList($groups),
            Config::GROUPS . ' must be number or list of numbers'
        );
        if (!self::isList($groups)) {
            return;
        }

        foreach ($groups as $group) {
            $this->assert(is_numeric($group), Config::GROUPS . " elements must be numbers: '$group'");
        }
    }


    /**
     * @param $stanza
     */
    private function checkAdmin($stanza)
    {
        if (isset($stanza[Config::ADMIN])) {
            $this->assert(
                is_string($stanza[Config::ADMIN]),
                "Admin must be string naming variant: '{$stanza[Config::ADMIN]}'"
            );
        }
    }

    /**
     * @param $stanza
     */
    private function checkInternal($stanza)
    {
        if (isset($stanza[Config::INTERNAL])) {
            $this->assert(
                is_string($stanza[Config::INTERNAL]),
                "Internal must be string naming variant: '{$stanza[Config::INTERNAL]}'"
            );
        }
    }

    /**
     * @param $stanza
     */
    private function checkPublicURLOverride($stanza)
    {
        if (!isset($stanza[Config::PUBLIC_URL_OVERRIDE])) {
            return;
        }

        $this->assert(
            is_bool($stanza[Config::PUBLIC_URL_OVERRIDE]),
            "public_url_override must be a boolean: '{$stanza[Config::PUBLIC_URL_OVERRIDE]}'"
        );
        if (is_bool($stanza[Config::PUBLIC_URL_OVERRIDE])) {
            $this->assert(
                $stanza[Config::PUBLIC_URL_OVERRIDE] === true,
                'Gratuitous public_url_override (defaults to false)'
            );
        }
    }

    /**
     * @param $stanza
     */
    private function checkBucketing($stanza)
    {
        if (!isset($stanza[Config::BUCKETING])) {
            return;
        }
        $this->assert(
            is_string($stanza[Config::BUCKETING]),
            "Non-string bucketing: '{$stanza[Config::BUCKETING]}'"
        );
        $this->assert(
            in_array($stanza[Config::BUCKETING], $this->_legal_bucketing_values),
            "Illegal bucketing: '{$stanza[Config::BUCKETING]}'"
        );
    }

    /**
     * @param $a
     * @return bool
     */
    private static function isList($a)
    {
        return is_array($a) and array_keys($a) === range(0, count($a) - 1);
    }
}
