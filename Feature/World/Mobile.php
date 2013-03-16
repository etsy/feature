<?php

/**
 * This sublcass of Feature_World overrides UAID and UserID so that
 * feature rampups can maintain consistency on mobile devices.
*/

class Feature_World_Mobile extends Feature_World {
    private $_udid;
    private $_userID;

    private $_name;
    private $_variant;
    private $_selector;

    public function __construct ($udid, $userID, $logger) {
        parent::__construct($logger);
        $this->_udid = $udid;
        $this->_userID = $userID;
    }

    public function uaid() {
        return $this->_udid;
    }

    public function userID () {
        return $this->_userID;
    }

    public function log ($name, $variant, $selector) {
        parent::log($name, $variant, $selector);

        $this->_name = $name;
        $this->_variant = $variant;
        $this->_selector = $selector;
    }

    public function getLastName() {
        return $this->_name;
    }

    public function getLastVariant() {
        return $this->_variant;
    }

    public function getLastSelector() {
        return $this->_selector;
    }

    public function clearLastFeature() {
        $this->_selector = null;
        $this->_name = null;
        $this->_variant = null;
    }

}
