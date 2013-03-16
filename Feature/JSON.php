<?php

/*
 * Utilities for getting existing configs and turning them into
 * JSON-encodeable data.
 */
class Feature_JSON {

    public static function stanza ($key, $server_config=null) {
        $stanza = self::findStanza($key, $server_config);
        return $stanza !== false ? self::translate($key, $stanza) : false;
    }

    public static function translate ($key, $stanza) {
        if (preg_match('/feature_.*_enabled$/', $key)) {
            return self::translateFeatureFlag($key, $stanza);
        } else if (substr($key, 0, 3) === 'ab.') {
            if (self::hasEnabled($stanza)) {
                return self::translateAB(substr($key, 3), $stanza);
            }
        } else if (substr($key, 0, 11) === 'new_config.') {
            return self::translateNewstyle(substr($key, 11), $stanza);
        } else {
            return self::translateOldstyle($key, $stanza);
        }
        return false;
    }

    public static function allKeys () {
        $server_config = $GLOBALS['server_config'];

        $keys = array();
        self::walk($server_config, null, $keys);
        return $keys;
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

    private static function walk ($config, $path, &$keys) {
        foreach ($config as $k => $v) {
            $new_path = $path === null ? $k : "$path.$k";
            if (self::hasEnabled($v)
                || preg_match('/^feature_/', $k)
                || (preg_match('/^new_config\./', $new_path) && self::newstyleEnabled($v))) {
                $keys[] = $new_path;
            }
            if (is_array($v)) {
                self::walk($v, $new_path, $keys);
            }
        }
    }


    private static function hasEnabled ($x) {
        return is_array($x) && array_key_exists('enabled', $x);
    }

    private static function newstyleEnabled ($x) {
        if (is_array($x)) {
            return array_key_exists('enabled', $x) ||
                array_key_exists('admin', $x) ||
                array_key_exists('internal', $x) ||
                array_key_exists('public_url_override', $x) ||
                array_key_exists('users', $x) ||
                array_key_exists('groups', $x) ||
                $x === array();
        } else {
            return $x === 'on'|| $x === 'off';
            // Missing winning variants but hard to distinguish them
            // from, e.g. 'enabled' => 'on'.
        }
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

    private static function translateNewstyle ($key, $value) {

        $spec = self::makeSpec($key);

        $internal_url = true;

        if (is_numeric($value)) {
            $value = array('enabled' => (int)$value);
        } else if (is_string($value)) {
            $value = array('enabled' => $value);
        }

        $enabled = Std::arrayVar($value, 'enabled', 0);
        $users   = self::expandUsersOrGroups(Std::arrayVar($value, 'users', array()));
        $groups  = self::expandUsersOrGroups(Std::arrayVar($value, 'groups', array()));

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

    private static function translateFeatureFlag ($key, $value) {
        $matches = array();
        if (preg_match('/^(.*)_enabled$/', $key, $matches)) {
            $k = $matches[1];
            $spec = self::makeSpec($k);
            if ($value === 1 || $value === 'on') {
                $spec['variants'][] = self::makeVariant('on', 100);
            } elseif ($value === 0 || $value === 'off') {
                $spec['variant'][] = self::makeVariant('on', 0);
            }
            return $spec;
        }
        return false;
    }

    private static function translateOldstyle ($key, $oldstyle) {

        $spec = self::makeSpec($key);
        $onVariant = self::makeVariant('on', 0);

        if (self::justOn($oldstyle)) {
            $onVariant['percentage'] = 100;

        } else if (self::justOff($oldstyle)) {
            $onVariant['percentage'] = 0;

        } else if (is_array($oldstyle) && array_key_exists('enabled', $oldstyle)) {

            if ($oldstyle['enabled'] === 'rampup') {
                if (array_key_exists('rampup', $oldstyle)) {
                    $rampup = $oldstyle['rampup'];
                    if (array_key_exists('percent', $rampup) && $rampup['percent']) {
                        $onVariant['percentage'] = $rampup['percent'];
                    }
                }
            } else if ($oldstyle['enabled'] === 'on') {
                $onVariant['percentage'] = 100;
            } else if ($oldstyle['enabled'] === 'off') {
                $onVariant['percentage'] = 0;
            }

            if (array_key_exists('rampup', $oldstyle)) {
                $rampup = $oldstyle['rampup'];

                if (array_key_exists('admin', $rampup) && self::trueish($rampup['admin'])) {
                    $spec['admin'] = 'on';
                }

                if (array_key_exists('internal', $rampup) && $rampup['internal'] === true) {
                    $spec['internal'] = 'on';
                }

                if (array_key_exists('random', $rampup) && $rampup['random'] === true) {
                    $spec['bucketing'] = 'random';
                }

                if (array_key_exists('whitelist', $rampup)) {
                    $onVariant['users'] = $rampup['whitelist'];
                }

                if (array_key_exists('group', $rampup)) {
                    if (is_array($rampup['group'])) {
                        $onVariant['groups'] = $rampup['group'];
                    } else if ($rampup['group'] > 0) {
                        // Check for group values > 0 since I'm pretty sure
                        // that's not a legal id. (Came up in one old config,
                        // at least.)
                        $onVariant['groups'] = array($rampup['group']);
                    }
                }
            }
        }

        $spec['variants'][] = $onVariant;
        return $spec;
    }

    private static function translateAB ($key, $value) {
        $spec = self::makeSpec($key);
        $whitelist_variant = null;
        $whitelist_users = null;

        if (array_key_exists('whitelist', $value)) {
            if (!array_key_exists('whitelist_variant', $value)) {
                Logger::log_info('No whitelist variant in ' . json_encode($value));
            } else {
                $whitelist_variant = $value['whitelist_variant'];
            }
            $whitelist_users = $value['whitelist'];
        }

        if ($value['enabled'] === 'on') {
            $percentages = self::weightsToPercentages($value['weights']);

            foreach ($percentages as $variant => $percentage) {
                $v = self::makeVariant($variant, $percentage);
                if ($variant === $whitelist_variant) {
                    $v['users'] = $whitelist_users;
                }
                $spec['variants'][] = $v;
            }

        } elseif ($value['enabled'] === 'off') {
            $spec['variants'][] = self::makeVariant('on', 0);
        }

        if (array_key_exists('admin', $value)) {
            $spec['admin'] = $value['admin'];
        }

        return $spec;
    }

    private static function weightsToPercentages ($weights) {

        $sum = 0;
        $percentages = array();

        foreach ($weights as $variant => $weight) {
            $sum += $weight;
        }
        foreach ($weights as $variant => $weight) {
            $percentages[$variant] = 100 * ($sum === 0 ? 0 : $weight/$sum);
        }
        return $percentages;
    }

    private static function justOn ($x) {
        return is_array($x) && sizeof($x) == 1 && array_key_exists('enabled', $x) &&
            ($x['enabled'] === 100 || $x['enabled'] === 'on');
    }

    private static function justOff ($x) {
        return is_array($x) && sizeof($x) == 1 && array_key_exists('enabled', $x) &&
            ($x['enabled'] === 0 || $x['enabled'] === 'off');
    }

    private static function trueish ($x) {
        return $x === true || $x === 'on' || $x === 'true';
    }
}