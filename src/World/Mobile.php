<?php

namespace CafeMedia\Feature\World;

use CafeMedia\Feature\Logger;
use CafeMedia\Feature\World;

/**
 * This subclass of World overrides UAID and UserID so that
 * feature rampups can maintain consistency on mobile devices.
*/

class Mobile extends World
{
    /**
     * @var
     */
    private $_udid;
    /**
     * @var
     */
    private $_userID;

    /**
     * @var
     */
    private $_name;
    /**
     * @var
     */
    private $_variant;
    /**
     * @var
     */
    private $_selector;

    /**
     * Mobile constructor.
     * @param $udid
     * @param $userID
     * @param Logger $logger
     */
    public function __construct ($udid, $userID, Logger $logger)
    {
        parent::__construct($logger);
        $this->_udid = $udid;
        $this->_userID = $userID;
    }

    /**
     * UAID of the current request.
     * @return mixed
     */
    public function uaid()
    {
        parent::uaid();
        return $this->_udid;
    }

    /**
     * @return mixed
     */
    public function userID ()
    {
        parent::userID();
        return $this->_userID;
    }

    /**
     * @param $name
     * @param $variant
     * @param $selector
     */
    public function log ($name, $variant, $selector)
    {
        parent::log($name, $variant, $selector);

        $this->_name = $name;
        $this->_variant = $variant;
        $this->_selector = $selector;
    }

    /**
     * @return mixed
     */
    public function getLastName()
    {
        return $this->_name;
    }

    /**
     * @return mixed
     */
    public function getLastVariant()
    {
        return $this->_variant;
    }

    /**
     * @return mixed
     */
    public function getLastSelector()
    {
        return $this->_selector;
    }

    public function clearLastFeature()
    {
        $this->_selector = null;
        $this->_name = null;
        $this->_variant = null;
    }
}
