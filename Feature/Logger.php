<?php

/**
 * Logging -- for each feature that is checked we can log that it was
 * checked, what variant was choosen, and why.
 */
class Feature_Logger {

    /*
     * Log that the feature $name was checked with $variant selected
     * by $selector. This is only called once per feature/bucketing id
     * per request.
     */
    public function log ($name, $variant, $selector) {
    }

}
