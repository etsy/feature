<?php

/**
 * Logging -- each feature that is checked needs to be logged to a
 * handful of different places. We incremenent a counter in StatsD,
 * stash some info via apache_note that will eventually be included in
 * the Apache logs and remember the selections made so we can include
 * them in the javascript for Google Analytics and our own event
 * logger.
 */
class Feature_Logger {

    const AB_SAMPLE_RATE               = 0.01;
    const APACHE_NOTE_NAME             = "etsy_ab_selections";
    const GA_MAX_CUSTOM_VAR_VAL_LENGTH = 62; // 64 - 2 bytes for 'AB'
    const GA_ERROR_COUNTER             = 'ab.ga_var_length_exceeded';
    const GA_SAMPLE_RATE               = 0.01;

    /*
     * Log that the feature $name was checked with $variant selected
     * by $selector. This is only called once per feature/bucketing id
     * per request.
     */
    public function log ($name, $variant, $selector) {
        // TODO: it'd be kind of nice if instead of logging to StatsD
        // and Apache on each new isEnabled check, we could do it all
        // at once near the end of the request based the selections
        // recorded in World.  like we do with the GA Javascript, etc.
        StatsD::increment("ab.$name.$variant", self::AB_SAMPLE_RATE);
        $this->logToApache($name, $variant);
    }

    // TODO: this should probably live elsewhere. Not sure where.
    public static function getGAJavascript ($selections) {

        $len = 0;
        $value = false;
        foreach ($selections as $selection) {
            list($name, $variant) = $selection; // GA doesn't care about selector.
            $pair = "$name.$variant";
            $len += strlen($pair);
            if ($len <= self::GA_MAX_CUSTOM_VAR_VAL_LENGTH) {
                $value = $value ? "$value..$pair" : $pair;
            } else {
                StatsD::increment(self::GA_ERROR_COUNTER, self::GA_SAMPLE_RATE);
                break;
            }
        }
        return $value ? "Etsy.GA.track(['_setCustomVar', 2, 'AB', '$value', 3]);" : '';
    }

    // TODO: this belongs in EventLogger/LogEvent.php once we've cut
    // over. Based on retrieveABAttributesNew which assumes
    // ab_selector_logging is enabled, which it has been since
    // 2012-01-09.
    public static function retrieveABAttributes () {
        $test_names     = array();
        $variant_names  = array();
        $selector_names = array();

        // New-style feature selections.
        foreach (Feature::selections() as $selection) {
            list($name, $variant, $selector) = $selection;
            $test_names[]     = urlencode($name);
            $variant_names[]  = urlencode($variant);
            $selector_names[] = urlencode($selector);
        }

        // Add old-style AB tests and rampups.
        $selector_map = AB2_Logger_EtsyLoggers::testVarMap()->getSelectorMap();
        foreach (AB2_Logger_EtsyLoggers::testVarMap()->getMap() as $t => $v) {
            $selector = isset($selector_map[$t]) ? $selector_map[$t] : null;

            if ($selector !== "w1") {
                $test_names[] = urlencode($t);
                $variant_names[] = urlencode($v);
                $selector_names[] = urlencode($selector);
            }
        }

        return array($test_names, $variant_names, $selector_names);
    }

    private function logToApache ($name, $variant) {
        if (function_exists("apache_note")) {
            // FIXME: Do we really need to prepend the 'ab_'? What
            // purpose does it serve?
            $sel = "ab_$name=$variant";
            $soFar = apache_note(self::APACHE_NOTE_NAME);
            $soFar = $soFar ? "$soFar|$sel" : $sel;
            apache_note(self::APACHE_NOTE_NAME, $soFar);
        }
    }
}
