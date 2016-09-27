<?php

namespace CafeMedia\Feature;

use Psr\Log\LoggerInterface;

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
 *
 * In addition, in order to support Smarty templates, which can't call
 * static methods, the getInstance() method returns a singleton object
 * that can be passed to templates and which provides the same API via
 * instance methods.
 *
 * Class Feature
 * @package CafeMedia\Feature
 */
class Feature
{
    /**
     * @var
     */
    private static $defaultWorld;

    /**
     * @var array
     */
    private static $configCache = array();

    /**
     * @var
     */
    private static $instance;

    /**
     * @var null
     */
    private static $logger = null;

    /**
     * @var LoggerInterface
     */
    private static $log;

    /**
     * @var array
     */
    private static $features = array();

    /**
     * @var string
     */
    private static $uaid = '';

    /**
     * @var string
     */
    private static $userID = '';

    /**
     * @var string
     */
    private static $userName = '';

    /**
     * @var null
     */
    private static $group = null;

    /**
     * @var string
     */
    private static $source = '';

    /**
     * @var bool
     */
    private static $isAdmin = false;

    /**
     * @var string
     */
    private static $url = '';

    /**
     * Feature constructor.
     * @param LoggerInterface $log
     * @param array $features
     * @param string $uaid
     * @param string $userID
     * @param string $userName
     * @param null $group
     * @param string $source
     * @param bool $isAdmin
     * @param string $url
     */
    public function __construct(
        LoggerInterface $log,
        array $features = array(),
        $uaid = '',
        $userID = '',
        $userName = '',
        $group = null,
        $source = '',
        $isAdmin = false,
        $url = ''
    ) {
        self::$log = $log;
        self::$features = $features;
        self::$uaid = $uaid;
        self::$userID = $userID;
        self::$userName = $userName;
        self::$group = $group;
        self::$source = $source;
        self::$isAdmin = $isAdmin;
        self::$url = $url;
    }

    /**
     * Get an object that can be passed to Smarty templates that wraps
     * our API with non-static methods of the same names and arguments.
     *
     * @return Instance
     */
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new Instance();
        }
        return self::$instance;
    }

    /**
     * Test whether the named feature is enabled for the current user.
     *
     * @static
     * @param string $name the config key for this feature.
     * @return bool
     */
    public static function isEnabled ($name)
    {
        return self::fromConfig($name)->isEnabled();
    }

    /**
     * Test whether the named feature is enabled for a given
     * user. This method should only be used when we want to bucket
     * based on a user other than the current logged in user, e.g. if
     * we are bucketing different listings based on their owner.
     *
     * @static
     * @param string $name the config key for this feature.
     *
     * @param $user - A user object whose id will be combined with $name
     * and hashed to get the bucketing.
     *
     * @return bool
     */
    public static function isEnabledFor($name, $user)
    {
        return self::fromConfig($name)->isEnabledFor($user);
    }

    /**
     * Test whether the named feature is enabled for a given
     * arbitrary string. This method should only be used when we want to bucket
     * based on something other than a user, e.g. shops, teams, treasuries, tags, etc.
     *
     * @static
     * @param string $name the config key for this feature.
     *
     * @param $string - A string which will be combined with $name
     * and hashed to get the bucketing.
     *
     * @return bool
     */
    public static function isEnabledBucketingBy($name, $string)
    {
        return self::fromConfig($name)->isEnabledBucketingBy($string);
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
     *
     * @static
     * @param string $name the config key for the feature.
     * @return mixed|string
     */
    public static function variant($name)
    {
        return self::fromConfig($name)->variant();
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
     *
     * @static
     *
     * @param string $name the config key for the feature.
     *
     * @param $user - A user object whose id will be combined with $name
     * and hashed to get the bucketing.
     * @return mixed|string
     */
    public static function variantFor($name, $user)
    {
        return self::fromConfig($name)->variantFor($user);
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
     *
     * @static
     *
     * @param string $name the config key for the feature.
     *
     * @param string $bucketingID A string to use as the bucketing ID.
     * @return mixed|string
     */
    public static function variantBucketingBy($name, $bucketingID)
    {
        return self::fromConfig($name)->variantBucketingBy($bucketingID);
    }

    /**
     * Description of the feature.
     *
     * @param $name
     * @return mixed|null
     */
    public static function description ($name)
    {
        return self::fromConfig($name)->description();
    }

    /**
     * Get data related to a Feature name: config must be nested
     * under the Feature name, in an array key named 'data'.
     *
     * @param string $name the Feature key to find data for
     * @param mixed $default what to return if not defined
     *
     * @return mixed
     */
    public static function data($name, $default = array())
    {
        return self::world()->configValue("$name.data", $default);
    }

    /**
     * Get data linked to a Feature name, specific for the enabled variant.
     * Nest data in an array named 'data' with a key for each variant.
     *
     * @param string $name the Feature key to find data for
     * @param mixed $default what to return if not found
     *
     * @return mixed
     */
    public static function variantData($name, $default = array())
    {
        $data    = self::data($name);
        $variant = self::variant($name);
        return isset($data[$variant]) ? $data[$variant] : $default;
    }

    /**
     * Get the named feature object. We cache the object after
     * building it from the config stanza to speed lookups.
     *
     * @static
     *
     * @param $name - name of the feature. Used as a key into the global config array
     *
     * @return Config
     */
    private static function fromConfig($name)
    {
        if (isset(self::$configCache[$name])) {
            return self::$configCache[$name];
        }

        $world = self::world();
        return self::$configCache[$name] = new Config($name, $world->configValue($name), $world, self::$logger);
    }

    /**
     * N.B. This method is for testing only. (The issue is that once a
     * Feature has been checked once, the result of the check is
     * cached but in tests we need to change the configuration and
     * have those changes be reflected in feature checks.)
     */
    public static function clearCacheForTests()
    {
        self::$configCache = array();
    }

    /**
     * Get the list of selections that have been made as an array of
     * (feature_name, variant_name, selector) arrays. This can be used
     * to record information about what features were associated with
     * what variants and why during the course of handling a request.
     *
     * @return array
     */
    public static function selections ()
    {
        return self::world()->selections();
    }

    /**
     * This API always uses the default World. Config takes
     * the world as an argument in order to ease unit testing.
     *
     * @return World
     */
    private static function world ()
    {
        if (!isset(self::$defaultWorld)) {
            self::$defaultWorld = new World(
                self::logger(),
                static::$features,
                static::$uaid,
                static::$userID,
                static::$userName,
                static::$group,
                static::$source,
                static::$isAdmin,
                static::$url
            );
        }
        return self::$defaultWorld;
    }

    /**
     * @return Logger|null
     */
    private static function logger ()
    {
        if (is_null(self::$logger)) {
            self::$logger = new Logger(self::$log);
        }
        return self::$logger;
    }
}
