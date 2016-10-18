<?php

namespace CafeMedia\Feature;

/**
 * Utility for turning configs into JSON-encodeable data.
 *
 * Class JSON
 * @package CafeMedia\Feature
 */
class JSON
{
    /**
     * Return the given config stanza as an array that can be json
     * encoded in a form that is slightly easier to deal with in
     * Javascript.
     *
     * @param $key
     * @param null $server_config
     * @return array|bool
     */
    public static function stanza ($key, $server_config = null)
    {
        $stanza = self::findStanza($key, $server_config);
        return $stanza !== false ? self::translate($key, $stanza) : false;
    }

    /**
     * @param $key
     * @param $cursor
     * @return bool|mixed
     */
    private static function findStanza($key, $cursor)
    {
        $step = strtok($key, '.');
        while ($step) {
            if (!is_array($cursor) || !isset($cursor[$step])) {
                return false;
            }

            $cursor = $cursor[$step];
            $step = strtok('.');
        }

        return $cursor;
    }

    /**
     * @param $key
     * @param $value
     * @return array
     */
    private static function translate ($key, $value)
    {
        $spec = self::makeSpec($key);

        $internal_url = true;

        if (is_numeric($value)) {
            $value = array('enabled' => (int)$value);
        }
        else if (is_string($value)) {
            $value = array('enabled' => $value);
        }

        $enabled = Util::arrayGet($value, 'enabled', 0);
        $users   = self::expandUsersOrGroups(Util::arrayGet($value, 'users', array()));
        $groups  = self::expandUsersOrGroups(Util::arrayGet($value, 'groups', array()));

        if ($enabled === 'off') {
            $spec['variants'][] = self::makeVariantWithUsersAndGroups('on', 0, $users, $groups);
            $internal_url = false;
        }
        else if (is_numeric($enabled)) {
            $spec['variants'][] = self::makeVariantWithUsersAndGroups('on', (int)$enabled, $users, $groups);
        }
        else if (is_string($enabled)) {
            $spec['variants'][] = self::makeVariantWithUsersAndGroups($enabled, 100, $users, $groups);
            $internal_url = false;
        }
        else if (is_array($enabled)) {
            foreach ($enabled as $v => $p) {
                if (is_numeric($p)) {
                    // Kind of a kludge. $p had better be numeric and
                    // there have been configs deployed where it
                    // wasn't which breaks the Catapult config history
                    // scripts. This will just skip those.
                    $spec['variants'][] = self::makeVariantWithUsersAndGroups($v, $p, $users, $groups);
                }
            }
        }
        $spec['internal_url_override'] = $internal_url;

        if (isset($value['admin'])) {
            $spec['admin'] = $value['admin'];
        }
        if (isset($value['internal'])) {
            $spec['internal'] = $value['internal'];
        }
        if (isset($value['bucketing'])) {
            $spec['bucketing'] = $value['bucketing'];
        }
        if (isset($value['internal'])) {
            $spec['internal'] = $value['internal'];
        }
        if (isset($value['public_url_override'])) {
            $spec['public_url_override'] = $value['public_url_override'];
        }

        return $spec;
    }

    /**
     * @param $key
     * @return array
     */
    private static function makeSpec ($key)
    {
        return array(
            'key' => $key,
            'internal_url_override' => false,
            'public_url_override' => false,
            'bucketing' => 'uaid',
            'admin' => null,
            'internal' => null,
            'variants' => array()
        );
    }

    /**
     * @param $name
     * @param $percentage
     * @return array
     */
    private static function makeVariant ($name, $percentage)
    {
        return array(
            'name' => $name,
            'percentage' => $percentage,
            'users' => array(),
            'groups' => array()
        );
    }

    /**
     * @param $name
     * @param $percentage
     * @param $users
     * @param $groups
     * @return array
     */
    private static function makeVariantWithUsersAndGroups ($name, $percentage, $users, $groups)
    {
        return array(
            'name'       => $name,
            'percentage' => $percentage,
            'users'      => self::extractForVariant($users, $name),
            'groups'     => self::extractForVariant($groups, $name),
        );
    }

    /**
     * @param $usersOrGroups
     * @param $name
     * @return array
     */
    private static function extractForVariant ($usersOrGroups, $name)
    {
        $result = array();
        foreach ($usersOrGroups as $thing => $variant) {
            if ($variant == $name) {
                $result[] = $thing;
            }
        }
        return $result;
    }

    /**
     * This is based on parseUsersOrGroups in Config. Probably
     * this logic should be put in that class in a form that we can
     * use.
     *
     * @param $value
     * @return array
     */
    private static function expandUsersOrGroups ($value)
    {
        if (is_string($value) || is_numeric($value)) {
            return array($value => Config::ON);
        }

        $result = array();

        if (self::isList($value)) {
            foreach ($value as $who) {
                $result[$who] = Config::ON;
            }
            return $result;
        }

        if (!is_array($value)) {
            return $result;
        }

        foreach ($value as $variant => $whos) {
            foreach (self::asArray($whos) as $who) {
                $result[$who] = $variant;
            }
        }

        return $result;
    }

    /**
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
}
