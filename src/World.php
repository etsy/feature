<?php

namespace CafeMedia\Feature;

/**
 * The interface Config needs to the outside world. This class
 * is used in the normal case but tests can use a mock
 * version. There's a reasonable argument that the code in Logger
 * should just be moved into this class since there's a fair bit of
 * passing stuff back and forth between here and Logger and Logger has
 * no useful independent existence.
 *
 * Class World
 * @package CafeMedia\Feature
 */
class World
{
    /**
     * @var Logger
     */
    private $_logger;
    /**
     * @var array
     */
    private $_selections = array();

    /**
     * @var array
     */
    private $features;

    /**
     * @var
     */
    private $uaid;

    /**
     * @var
     */
    private $userID;

    /**
     * @var
     */
    private $userName = '';

    /**
     * @var
     */
    private $group;

    /**
     * @var
     */
    private $source;

    /**
     * @var array
     */
    private $adminIds;

    /**
     * @var string
     */
    private $url = '';

    /**
     * World constructor.
     * @param Logger $logger
     * @param array $features
     * @param string $uaid
     * @param string $userID
     * @param string $userName
     * @param null $group
     * @param string $source
     * @param array $adminIds
     * @param string $url
     */
    public function __construct (
        Logger $logger,
        array $features = array(),
        $uaid = '',
        $userID = '',
        $userName = '',
        $group = null,
        $source = '',
        array $adminIds = array(),
        $url = ''
    ) {
        $this->_logger = $logger;
        $this->features = $features;
        $this->uaid = $uaid;
        $this->userID = $userID;
        $this->userName = $userName;
        $this->group = $group;
        $this->source = $source;
        $this->adminIds = $adminIds;
        $this->url = $url;
    }

    /**
     * Get the config value for the given key.
     *
     * @param $name
     * @param null $default
     * @return null
     */
    public function configValue($name, $default = null)
    {
        //return $default; // IMPLEMENT FOR YOUR CONTEXT
        if (isset($this->features[$name])) {
            return $this->features[$name];
        }
        return $default;
    }

    /**
     * UAID of the current request.
     */
    public function uaid()
    {
        //return null; // IMPLEMENT FOR YOUR CONTEXT
        return $this->uaid;
    }

    /**
     * User ID of the currently logged in user or null.
     */
    public function userID ()
    {
        //return null; // IMPLEMENT FOR YOUR CONTEXT
        return $this->userID;
    }

    /**
     * Login name of the currently logged in user or null. Needs the
     * ORM. If we're running as part of an Atlas request we ignore the
     * passed in userID and return instead the Atlas user name.
     */
    public function userName ()
    {
        //return null; // IMPLEMENT FOR YOUR CONTEXT
        return $this->userName;
    }

    /**
     * Is the vistor in a specific group?
     * @param $groupID
     * @return bool
     */
    public function viewingGroup($groupID)
    {
        return is_object($this->group) && method_exists($this->group, 'getId') && $this->group->getId() == $groupID;
    }

    /**
     * Is the vistor from a particular source?
     *
     * @param $source
     * @return bool
     */
    public function isSource($source)
    {
        return $this->source == $source;
    }

    /**
     * Is the given user a member of the given group? (This currently,
     * like the old config system, uses numeric group IDs in the
     * config file, in order to speed up the lookup--the numeric ID is
     * the primary key and we save having to look up the group by
     * name.)
     * @param null $userID
     * @param null $groupID
     * @return bool
     */
    public function inGroup ($userID = null, $groupID = null)
    {
        //return null; // IMPLEMENT FOR YOUR CONTEXT
        if (is_object($this->group) && method_exists($this->group, 'isMember')) {
            return $this->group->isMember();
        }

        return false;
    }

    /**
     * Is the current user an admin?
     *
     * @param $userID - the id of the relevant user, either the
     * currently logged in user or some other user.
     * @return bool
     */
    public function isAdmin ($userID)
    {
        //return false; // IMPLEMENT FOR YOUR CONTEXT

        return in_array($userID, $this->adminIds);
    }

    /**
     * Is this an internal request?
     */
    public function isInternalRequest ()
    {
        return false; // IMPLEMENT FOR YOUR CONTEXT
        // TODO: list local ips
    }

    /**
     * 'features' query param for url overrides.
     *
     * @return string
     */
    public function urlFeatures ()
    {
        return !empty($this->url) ? $this->url : '';
    }

    /**
     * Produce a random number in [0, 1) for RANDOM bucketing.
     *
     * @return float|int
     */
    public function random ()
    {
        return mt_rand(0, mt_getrandmax() - 1) / mt_getrandmax();
    }

    /**
     * Produce a randomish number in [0, 1) based on the given id.
     *
     * @param $id
     * @return float
     */
    public function hash ($id)
    {
        return self::mapHex(hash('sha256', $id));
    }

    /**
     * Record that $variant has been selected for feature named $name
     * by $selector and pass the same information along to the logger.
     *
     * @param $name
     * @param $variant
     * @param $selector
     */
    public function log ($name, $variant, $selector)
    {
        $this->_selections[] = array($name, $variant, $selector);
        $this->_logger->log($name, $variant, $selector);
    }

    /**
     * Get the list of selections that we have recorded. The public
     * API for getting at the selections is Feature::selections which
     * should be the only caller of this method.
     *
     * @return array
     */
    public function selections ()
    {
        return $this->_selections;
    }

    /**
     * Map a hex value to the half-open interval [0, 1) while
     * preserving uniformity of the input distribution.
     *
     * @param string $hex a hex string
     * @return float
     */
    private static function mapHex($hex)
    {
        $len = min(30, strlen($hex));
        $vMax = 1 << $len;
        $v = 0;
        for ($i = 0; $i < $len; ++$i) {
            $bit = hexdec($hex[$i]) < 8 ? 0 : 1;
            $v = ($v << 1) + $bit;
        }

        return $v / $vMax;
    }
}
