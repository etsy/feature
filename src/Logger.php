<?php

namespace CafeMedia\Feature;

/**
 * Logging -- for each feature that is checked we can log that it was
 * checked, what variant was choosen, and why.
 */
/**
 * Class Logger
 * @package CafeMedia\Feature
 */
class Logger {

    /*
     * Log that the feature $name was checked with $variant selected
     * by $selector. This is only called once per feature/bucketing id
     * per request.
     */
    /**
     * @param $name
     * @param $variant
     * @param $selector
     */
    public function log ($name, $variant, $selector) {}
}
