<?php

/**
 * The public API testing whether a specific feature is enabled and,
 * if so, what variant should be used.
 *
 * Primary public API:
 *
 *   Feature::isEnabled('foo', $data = null);
 *   Feature::variant('foo', $data = null);
 *
 * What the $data value should be depends on the experiment unit the
 * feature is configured to use. Often the default experimental unit
 * will simply be based on something in the global environment such as
 * a web request from which we can obtain user information.
 *
 * In addition, in order to support Smarty templates, which can't call
 * static methods, the getInstance() method returns a singleton object
 * that can be passed to templates and which provides the same API via
 * instance methods.
 */
class Feature {

    private static $defaultWorld;
    private static $configCache = array();
    private static $instance;

    /**
     * Get an object that can be passed to Smarty templates that wraps
     * our API with non-static methods of the same names and arguments.
     */
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new Feature_Instance();
        }
        return self::$instance;
    }

    /**
     * Test whether the named feature is enabled.
     *
     * @static
     * @param string $name the config key for this feature.
     * @return bool
     */
    public static function isEnabled ($name, $data = null) {
        return self::fromConfig($name)->isEnabled($data);
    }


    /**
     * Get the name of the A/B variant for the named feature. Logs an
     * error if called when isEnabled($name) doesn't return true.
     * (I.e. calls to this method should only occur in blocks guarded
     * by an isEnabled check.)
     *
     * Also logs an error if 'enabled' is 'on' for the named feature
     * since there should be no variant-dependent code left when a
     * feature has been fully enabled. To clean up a finished
     * experiment, first set 'enabled' to the name of the winning
     * variant.
     *
     * @static
     * @param string $name the config key for the feature.
     */
    public static function variant($name, $data = null) {
        return self::fromConfig($name)->variant($data);
    }

    /**
     * Get data related to a Feature name: config must be nested
     * under the Feature name, in an array key named 'data'.
     * @param string $name the Feature key to find data for
     * @param mixed $default what to return if not defined
     *
     * @return mixed
     */
    public static function data($name, $default = array()) {
        return self::world()->configValue("$name.data", $default);
    }

    /**
     * Get data linked to a Feature name, specific for the enabled variant.
     * Nest data in an array named 'data' with a key for each variant.
     * @param string $name the Feature key to find data for
     * @param mixed $default what to return if not found
     *
     * @return mixed
     */
    public static function variantData($name, $default = array()) {
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
     * @param $name name of the feature. Used as a key into the global config array
     *
     * @return Feature_Config
     */
    private static function fromConfig($name) {
        if (array_key_exists($name, self::$configCache)) {
            return self::$configCache[$name];
        } else {
            $world = self::world();
            $stanza = $world->configValue($name);
            return self::$configCache[$name] = new Feature_Config($name, $stanza, $world);
        }
    }

    /**
     * N.B. This method is for testing only. (The issue is that once a
     * Feature has been checked once, the result of the check is
     * cached but in tests we need to change the configuration and
     * have those changes be reflected in feature checks.)
     */
    public static function clearCacheForTests() {
        self::$configCache = array();
    }


    /**
     * Get the list of selections that have been made as an array of
     * (feature_name, variant_name, selector) arrays. This can be used
     * to record information about what features were associated with
     * what variants and why during the course of handling a request.
     */
    public static function selections () {
        return self::world()->selections();
    }

    /**
     * This API always uses the default World. Feature_Config takes
     * the world as an argument in order to ease unit testing.
     */
    private static function world () {
        if (!isset(self::$defaultWorld)) {
            self::$defaultWorld = new Feature_World(new Feature_Logger());
        }
        return self::$defaultWorld;
    }
}
