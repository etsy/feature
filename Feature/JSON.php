<?php

/*
 * Utility for turning configs into JSON-encodeable data.
 */
class Feature_JSON {

    /*
     * Return the given config stanza as an array that can be json
     * encoded in a form that is slightly easier to deal with in
     * Javascript.
     */
    public static function stanza ($key, $server_config=null) {
        $stanza = self::findStanza($key, $server_config);
        return $stanza !== false ? self::translate($key, $stanza) : false;
    }

    private static function findStanza($key, $cursor) {
        $step = strtok($key, '.');
        while ($step) {
            if (is_array($cursor) && array_key_exists($step, $cursor)) {
                $cursor = $cursor[$step];
            } else {
                return false;
            }
            $step = strtok('.');
        }
        return $cursor;
    }

    private static function translate ($key, $value) {

        $spec = self::makeSpec($key);

        $internal_url = true;

        if (is_numeric($value)) {
            $value = array('enabled' => (int)$value);
        } else if (is_string($value)) {
            $value = array('enabled' => $value);
        }

        $enabled = Feature_Util::arrayGet($value, 'enabled', 0);
        $users   = self::expandUsersOrGroups(Feature_Util::arrayGet($value, 'users', array()));
        $groups  = self::expandUsersOrGroups(Feature_Util::arrayGet($value, 'groups', array()));

        if ($enabled === 'off') {
            $spec['variants'][] = self::makeVariantWithUsersAndGroups('on', 0, $users, $groups);
            $internal_url = false;
        } else if (is_numeric($enabled)) {
            $spec['variants'][] = self::makeVariantWithUsersAndGroups('on', (int)$enabled, $users, $groups);
        } else if (is_string($enabled)) {
            $spec['variants'][] = self::makeVariantWithUsersAndGroups($enabled, 100, $users, $groups);
            $internal_url = false;
        } else if (is_array($enabled)) {
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

        if (array_key_exists('admin', $value)) {
            $spec['admin'] = $value['admin'];
        }
        if (array_key_exists('internal', $value)) {
            $spec['internal'] = $value['internal'];
        }
        if (array_key_exists('bucketing', $value)) {
            $spec['bucketing'] = $value['bucketing'];
        }
        if (array_key_exists('internal', $value)) {
            $spec['internal'] = $value['internal'];
        }
        if (array_key_exists('public_url_override', $value)) {
            $spec['public_url_override'] = $value['public_url_override'];
        }

        return $spec;
    }

    private static function makeSpec ($key) {
        return array(
            'key' => $key,
            'internal_url_override' => false,
            'public_url_override' => false,
            'bucketing' => 'uaid',
            'admin' => null,
            'internal' => null,
            'variants' => array());
    }

    private static function makeVariant ($name, $percentage) {
        return array(
            'name' => $name,
            'percentage' => $percentage,
            'users' => array(),
            'groups' => array());
    }

    private static function makeVariantWithUsersAndGroups ($name, $percentage, $users, $groups) {
        return array(
            'name'       => $name,
            'percentage' => $percentage,
            'users'      => self::extractForVariant($users, $name),
            'groups'     => self::extractForVariant($groups, $name),
        );
    }

    private static function extractForVariant ($usersOrGroups, $name) {
        $result = array();
        foreach ($usersOrGroups as $thing => $variant) {
            if ($variant == $name) {
                $result[] = $thing;
            }
        }
        return $result;
    }

    // This is based on parseUsersOrGroups in Feature_Config. Probably
    // this logic should be put in that class in a form that we can
    // use.
    private static function expandUsersOrGroups ($value) {
        if (is_string($value) || is_numeric($value)) {
            return array($value => Feature_Config::ON);

        } elseif (self::isList($value)) {
            $result = array();
            foreach ($value as $who) {
                $result[$who] = Feature_Config::ON;
            }
            return $result;

        } elseif (is_array($value)) {
            $result = array();
            foreach ($value as $variant => $whos) {
                foreach (self::asArray($whos) as $who) {
                    $result[$who] = $variant;
                }
            }
            return $result;

        } else {
            return array();
        }
    }

    private static function isList($a) {
        return is_array($a) and array_keys($a) === range(0, count($a) - 1);
    }

    private static function asArray ($x) {
        return is_array($x) ? $x : array($x);
    }

}