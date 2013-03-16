<?php

/**
 * A thin wrapper around the static Feature API for use in
 * templates. The singleton instance of this class should be obtained
 * via Feature::getInstance().
 */
class Feature_Instance {

    /**
     * Wrapper for Feature::isEnabled($name).
     */
    public function isEnabled ($name) {
        return Feature::isEnabled($name);
    }

    /**
     * Wrapper for Feature::isEnabledFor($name, $user).
     */
    public function isEnabledFor($name, $user) {
        return Feature::isEnabledFor($name, $user);
    }

    /**
     * Wrapper for Feature::isEnabledBucketingBy($name, $string).
     */
    public function isEnabledBucketingBy($name, $string) {
        return Feature::isEnabledBucketingBy($name, $string);
    }

    /**
     * Wrapper for Feature::variant($name).
     */
    public function variant($name) {
        return Feature::variant($name);
    }

    /**
     * Wrapper for Feature::variantFor($name, $user).
     */
    public function variantFor($name, $user) {
        return Feature::variantFor($name, $user);
    }

    /**
     * Wrapper for Feature::variantBucketingBy($name, $bucketingID).
     */
    public function variantBucketingBy($name, $bucketingID) {
        return Feature::variantBucketingBy($name, $bucketingID);
    }

}
