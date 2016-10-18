<?php

namespace CafeMedia\Feature;

/**
 * A thin wrapper around the static Feature API for use in
 * templates. The singleton instance of this class should be obtained
 * via Feature::getInstance().
 *
 * Class Instance
 * @package CafeMedia\Feature
 */
class Instance
{
    /**
     * Wrapper for Feature::isEnabled($name).
     * @param $name
     * @return bool
     */
    public function isEnabled ($name)
    {
        return Feature::isEnabled($name);
    }

    /**
     * Wrapper for Feature::isEnabledFor($name, $user).
     * @param $name
     * @param $user
     * @return bool
     */
    public function isEnabledFor($name, $user)
    {
        return Feature::isEnabledFor($name, $user);
    }

    /**
     * Wrapper for Feature::isEnabledBucketingBy($name, $string).
     * @param $name
     * @param $string
     * @return bool
     */
    public function isEnabledBucketingBy($name, $string)
    {
        return Feature::isEnabledBucketingBy($name, $string);
    }

    /**
     * Wrapper for Feature::variant($name).
     * @param $name
     * @return mixed|string
     */
    public function variant($name)
    {
        return Feature::variant($name);
    }

    /**
     * Wrapper for Feature::variantFor($name, $user).
     * @param $name
     * @param $user
     * @return mixed|string
     */
    public function variantFor($name, $user)
    {
        return Feature::variantFor($name, $user);
    }

    /**
     * Wrapper for Feature::variantBucketingBy($name, $bucketingID).
     * @param $name
     * @param $bucketingID
     * @return mixed|string
     */
    public function variantBucketingBy($name, $bucketingID)
    {
        return Feature::variantBucketingBy($name, $bucketingID);
    }

    /**
     * @param string $format
     * @return string
     */
    public function getGACustomVarJS($format = 'web')
    {
        $types = array(
            'web'    => array('prepend' => '_gaq.push(', 'append' => ');'),
            'mobile' => array('prepend' => '',           'append' => ','),
        );
        if (!isset($types[$format])) {
            $format = 'web';
        }

        return "{$types[$format]['prepend']}['_setCustomVar', 3, 'AB', 'null', 3]{$types[$format]['append']}";
    }
}
