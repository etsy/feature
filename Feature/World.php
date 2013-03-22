<?php

/**
 * The interface Feature_Config needs to the outside world. This class
 * is used in the normal case but tests can use a mock
 * version. There's a reasonable argument that the code in Logger
 * should just be moved into this class since there's a fair bit of
 * passing stuff back and forth between here and Logger and Logger has
 * no useful independent existence.
 */
class Feature_World {

    const DEFAULT_UNIT = 'request';

    private $_logger;
    private $_selections = array();

    public function __construct ($logger) {
        $this->_logger = $logger;
    }

    /*
     * Get an ExperimentalUnit object.
     */
    public function unit($unit = null) {
        if (is_null($unit)) {
            $unit = self::DEFAULT_UNIT;
        }

        switch ($unit) {
        case 'request':
            return new Feature_EtsyRequestUnit();
        case 'query':
            return new Feature_EtsySearchQueryUnit();
        default:
            throw new InvalidArgumentException("Bad unit type: $unit");
        }
    }

    /*
     * Get the config value for the given key.
     */
    public function configValue($name, $default = null) {
        return $default; // IMPLEMENT FOR YOUR CONTEXT
    }

    /*
     * Produce a random number in [0, 1) for RANDOM bucketing.
     */
    public function random () {
        return mt_rand(0, mt_getrandmax() - 1) / mt_getrandmax();
    }

    /*
     * Produce a randomish number in [0, 1) based on the given id.
     */
    public function hash ($id) {
        return self::mapHex(hash('sha256', $id));
    }

    /*
     * Record that $variant has been selected for feature named $name
     * by $selector and pass the same information along to the logger.
     */
    public function log ($name, $variant, $selector) {
        $this->_selections[] = array($name, $variant, $selector);
        $this->_logger->log($name, $variant, $selector);
    }

    /*
     * Get the list of selections that we have recorded. The public
     * API for getting at the selections is Feature::selections which
     * should be the only caller of this method.
     */
    public function selections () {
        return $this->_selections;
    }

    /**
     * Map a hex value to the half-open interval [0, 1) while
     * preserving uniformity of the input distribution.
     *
     * @param string $hex a hex string
     * @return float
     */
    private static function mapHex($hex) {
        $len = min(40, strlen($hex));
        $vMax = 1 << $len;
        $v = 0;
        for ($i = 0; $i < $len; $i++) {
            $bit = hexdec($hex[$i]) < 8 ? 0 : 1;
            $v = ($v << 1) + $bit;
        }
        $w = $v / $vMax;
        return $w;
    }
}
