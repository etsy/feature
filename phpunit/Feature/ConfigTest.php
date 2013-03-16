<?php
require_once "Loader.php";

/*
 * Test cases:
 *
 * enabled: on, off, number, array
 * users: none, single, list, array
 * groups: none, single, list, array
 * admin: variant
 * internal: variant
 * public_url_overrride: absent, true, false
 * bucketing: 'user', 'uaid', 'random'
 */
class Feature_ConfigTest extends PHPUnit_Framework_TestCase {

    function testDefaultDisabled () {
        $c = null;
        $this->expectDisabled($c, array('uaid' => 0));
        $this->expectDisabled($c, array('uaid' => 1));
    }

    function testFullyEnabled() {
        $c = array('enabled' => 'on');
        $this->expectEnabled($c, array('uaid' => '0'));
        $this->expectEnabled($c, array('uaid' => '1'));
    }

    function testSimpleDisabled () {
        $c = array('enabled' => 'off');
        $this->expectDisabled($c, array('uaid' => '0'));
        $this->expectDisabled($c, array('uaid' => '1'));
    }

    function testVariantEnabled () {
        $c = array('enabled' => 'winner');
        $this->expectEnabled($c, array('uaid' => '0'), 'winner');
        $this->expectEnabled($c, array('uaid' => '1'), 'winner');
    }

    function testFullyEnabledString() {
        $c = 'on';
        $this->expectEnabled($c, array('uaid' => '0'));
        $this->expectEnabled($c, array('uaid' => '1'));
    }

    function testSimpleDisabledString () {
        $c = 'off';
        $this->expectDisabled($c, array('uaid' => '0'));
        $this->expectDisabled($c, array('uaid' => '1'));
    }

    function testVariantEnabledString () {
        $c = 'winner';
        $this->expectEnabled($c, array('uaid' => '0'), 'winner');
        $this->expectEnabled($c, array('uaid' => '1'), 'winner');
    }

    function testSimpleRampup () {
        $c = array('enabled' => '50');
        $this->expectEnabled($c, array('uaid' => '0'));
        $this->expectEnabled($c, array('uaid' => '.1'));
        $this->expectEnabled($c, array('uaid' => '.4999'));
        $this->expectDisabled($c, array('uaid' => '.5'));
        $this->expectDisabled($c, array('uaid' => '.6'));
        $this->expectDisabled($c, array('uaid' => '.99'));
        $this->expectDisabled($c, array('uaid' => '1'));
    }

    function testMultivariant () {
        $c = array('enabled' => array('foo' => 2, 'bar' => 3));
        $this->expectEnabled($c, array('uaid' => '0'), 'foo');
        $this->expectEnabled($c, array('uaid' => '.01'), 'foo');
        $this->expectEnabled($c, array('uaid' => '.01999'), 'foo');
        $this->expectEnabled($c, array('uaid' => '.02'), 'bar');
        $this->expectEnabled($c, array('uaid' => '.04999'), 'bar');
        $this->expectDisabled($c, array('uaid' => '.05'));
        $this->expectDisabled($c, array('uaid' => '1'));
    }

    /*
     * Is feature disbaled by enabled => off despite every other
     * setting trying to turn it on?
     */
    function testComplexDisabled () {
        $c = array(
            'enabled'              => 'off',
            'users'                => array('fred', 'sally'),
            'groups'               => array(1234, 2345),
            'admin'                => 'on',
            'internal'             => 'on',
            'public_url_overrride' => true
        );

        $this->expectDisabled($c, array('isInternal' => true, 'uaid' => '0'));
        $this->expectDisabled($c, array('userName'   => 'fred', 'uaid' => '0'));
        $this->expectDisabled($c, array('inGroup'    => array(0 => 1234), 'uaid' => '0'));
        $this->expectDisabled($c, array('uaid'       => '100', 'uaid' => '0'));
        $this->expectDisabled($c, array('isAdmin'    => true, 'uaid' => '0'));
        $this->expectDisabled($c, array('isInternal' => true, 'urlFeatures' => 'foo', 'uaid' => 0));

        // Now all at once.
        $this->expectDisabled($c, array(
            'isInternal'  => true,
            'userName'    => 'fred',
            'inGroup'     => array(0 => 1234),
            'uaid'        => '100',
            'isAdmin'     => true,
            'urlFeatures' => 'foo',
            'userID'      => '0'));
    }

    function testAdminOnly () {
        $c = array('enabled' => 0, 'admin' => 'on');
        $this->expectEnabled($c, array('isAdmin' => true, 'uaid' => '0', 'userID' => '1'));
        $this->expectDisabled($c, array('isAdmin' => false, 'uaid' => '1', 'userID' => '1'));
    }

    function testAdminPlusSome () {
        $c = array('enabled' => 10, 'admin' => 'on');
        $this->expectEnabled($c, array('isAdmin' => true, 'uaid' => '.5', 'userID' => '1'));
        $this->expectEnabled($c, array('isAdmin' => false, 'uaid' => '.05', 'userID' => '1'));
        $this->expectDisabled($c, array('isAdmin' => false, 'uaid' => '.5', 'userID' => '1'));
    }

    function testInternalOnly () {
        $c = array('enabled' => 0, 'internal' => 'on');
        $this->expectEnabled($c, array('isInternal' => true, 'uaid' => '0'));
        $this->expectDisabled($c, array('isInternal' => false, 'uaid' => '1'));
    }

    function testInternalPlusSome () {
        $c = array('enabled' => 10, 'internal' => 'on');
        $this->expectEnabled($c, array('isInternal' => true, 'uaid' => '.5'));
        $this->expectEnabled($c, array('isInternal' => false, 'uaid' => '.05'));
        $this->expectDisabled($c, array('isInternal' => false, 'uaid' => '.5'));
    }

    function testOneUser () {
        $c = array('enabled' => 0, 'users' => 'fred');
        $this->expectEnabled($c, array('uaid' => '1', 'userName' => 'fred', 'userID' => '1'));
        $this->expectDisabled($c, array('uaid' => '1', 'userName' => 'george', 'userID' => '1'));
        $this->expectDisabled($c, array('userID' => null, 'uaid' => 0));
    }

    function testListOfOneUser () {
        $c = array('enabled' => 0, 'users' => array('fred'));
        $this->expectEnabled($c, array('uaid' => '1', 'userName' => 'fred', 'userID' => '1'));
        $this->expectDisabled($c, array('uaid' => '1', 'userName' => 'george', 'userID' => '1'));
    }

    function testListOfUsers () {
        $c = array('enabled' => 0, 'users' => array('fred', 'ron'));
        $this->expectEnabled($c, array('uaid' => '1', 'userName' => 'fred', 'userID' => '1'));
        $this->expectEnabled($c, array('uaid' => '1', 'userName' => 'ron', 'userID' => '1'));
        $this->expectDisabled($c, array('uaid' => '1', 'userName' => 'george', 'userID' => '1'));
    }
    
    function testListOfUsersCaseInsensitive() {
        $c = array('enabled' => 0, 'users' => array('fred', 'FunGuy'));
        $this->expectEnabled($c, array('uaid' => '1', 'userName' => 'fred', 'userID' => '1'));
        $this->expectEnabled($c, array('uaid' => '1', 'userName' => 'FunGuy', 'userID' => '1'));
        $this->expectEnabled($c, array('uaid' => '1', 'userName' => 'FUNGUY', 'userID' => '1'));
        $this->expectEnabled($c, array('uaid' => '1', 'userName' => 'funguy', 'userID' => '1'));
    }

    function testArrayOfUsers () {
        // It might be kind of nice to allow 'enabled' => 0 here but
        // then we lose the ability to check that the variants
        // mentioned in a users clause are actually valid
        // variants. Which maybe is okay: perhaps we'd like to be able
        // to enable variants for users that are otherwise disabled.
        $c = array('enabled' => array('twins' => 0, 'other' => 0),
                   'users' => array(
                                    'twins' => array('fred', 'george'),
                                    'other' => 'ron'));
        $this->expectEnabled($c, array('uaid' => '1', 'userName' => 'fred', 'userID' => '1'), 'twins');
        $this->expectEnabled($c, array('uaid' => '1', 'userName' => 'george', 'userID' => '2'), 'twins');
        $this->expectEnabled($c, array('uaid' => '1', 'userName' => 'ron', 'userID' => '3'), 'other');
        $this->expectDisabled($c, array('uaid' => '0', 'userName' => 'percy', 'userID' => '4'));
    }

    function testOneGroup () {
        $c = array('enabled' => 0, 'groups' => 1234);
        $this->expectEnabled($c, array('uaid' => 1, 'userID' => 1, 'inGroup' => array(1 => array(1234))));
        $this->expectDisabled($c, array('uaid' => 0, 'userID' => 2, 'inGroup' => array(2 => array(2345))));
        $this->expectDisabled($c, array('uaid' => 0, 'userID' => null, 'uaid' => 0));
    }

    function testListOfOneGroup () {
        $c = array('enabled' => 0, 'groups' => array(1234));
        $this->expectEnabled($c, array('uaid' => 1, 'userID' => 1, 'inGroup' => array(1 => array(1234))));
        $this->expectDisabled($c, array('uaid' => 0, 'userID' => 2, 'inGroup' => array(2 => array(2345))));
    }

    function testListOfGroups () {
        $c = array('enabled' => 0, 'groups' => array(1234, 2345));
        $this->expectEnabled($c, array('uaid' => 1, 'userID' => 1, 'inGroup' => array(1 => array(1234))));
        $this->expectEnabled($c, array('uaid' => 1, 'userID' => 2, 'inGroup' => array(2 => array(2345))));
        $this->expectDisabled($c, array('uaid' => 0, 'userID' => 3, 'inGroup' => array(3 => array())));
    }
    function testArrayOfGroups () {
        // See comment at testArrayOfUsers; similar issue applies here.
        $c = array('enabled' => array('twins' => 0, 'other' => 0),
                   'groups' => array(
                                     'twins' => array(1234, 2345),
                                     'other' => 3456));
        $this->expectEnabled($c, array('uaid' => 1, 'userID' => 1, 'inGroup' => array(1 => array(1234))), 'twins');
        $this->expectEnabled($c, array('uaid' => 1, 'userID' => 2, 'inGroup' => array(2 => array(2345))), 'twins');
        $this->expectEnabled($c, array('uaid' => 1, 'userID' => 3, 'inGroup' => array(3 => array(3456))), 'other');
        $this->expectDisabled($c, array('uaid' => 0, 'userID' => 4, 'inGroup' => array(4 => array())));
    }

    function testUrlOverride () {
        $c = array('enabled' => 0);
        $this->expectEnabled($c, array('uaid' => '1', 'isInternal' => true, 'urlFeatures' => 'foo'));
        $this->expectEnabled($c, array('uaid' => '1', 'isInternal' => true, 'urlFeatures' => 'foo:on'));
        $this->expectEnabled($c, array('uaid' => '1', 'isInternal' => true, 'urlFeatures' => 'foo:bar'), 'bar');
        $this->expectDisabled($c, array('uaid' => '1', 'isInternal' => false, 'urlFeatures' => 'foo'));
        $this->expectDisabled($c, array('uaid' => '1', 'isInternal' => false, 'urlFeatures' => 'foo:on'));
        $this->expectDisabled($c, array('uaid' => '1', 'isInternal' => false, 'urlFeatures' => 'foo:bar'));
    }

    function testPublicUrlOverride () {
        $c = array('enabled' => 0, 'public_url_override' => true);
        $this->expectEnabled($c, array('uaid' => '1', 'isInternal' => true, 'urlFeatures' => 'foo'));
        $this->expectEnabled($c, array('uaid' => '1', 'isInternal' => true, 'urlFeatures' => 'foo:on'));
        $this->expectEnabled($c, array('uaid' => '1', 'isInternal' => true, 'urlFeatures' => 'foo:bar'), 'bar');
        $this->expectEnabled($c, array('uaid' => '1', 'isInternal' => false, 'urlFeatures' => 'foo'));
        $this->expectEnabled($c, array('uaid' => '1', 'isInternal' => false, 'urlFeatures' => 'foo:on'));
        $this->expectEnabled($c, array('uaid' => '1', 'isInternal' => false, 'urlFeatures' => 'foo:bar'), 'bar');
    }

    function testBucketBy () {
        $c = array('enabled' => 2, 'bucketing' => 'user');
        $this->expectEnabled($c, array('uaid' => 1, 'userID' => .01));
        $this->expectDisabled($c, array('uaid' => 0, 'userID' => .03));
    }

    function testUAIDFallback () {
        $c = array('enabled' => 2, 'bucketing' => 'user');
        $this->expectEnabled($c, array('userID' => null, 'uaid' => .01));
        $this->expectDisabled($c, array('userID' => null, 'uaid' => .03));
    }

    /*
     * Ignore userID and uuaid in favor of random numbers for bucketing.
     */
    function testRandom () {
        $c = array('enabled' => 3, 'bucketing' => 'random');
        $this->expectEnabled($c, array('uaid' => 1, 'random' => .00));
        $this->expectEnabled($c, array('uaid' => 1, 'random' => .01));
        $this->expectEnabled($c, array('uaid' => 1, 'random' => .02));
        $this->expectEnabled($c, array('uaid' => 1, 'random' => .02999));
        $this->expectDisabled($c, array('uaid' => 0, 'random' => .03));
        $this->expectDisabled($c, array('uaid' => 0, 'random' => .04));
        $this->expectDisabled($c, array('uaid' => 0, 'random' => .99999));
    }

    /*
     * Somewhat indirect test that we cache the value by id: even if
     * the config is set up to use a random bucket (i.e. indpendent of
     * the id) it should still return the same value for the same id
     * which we test by having the two 'random' values returned by the
     * test world be ones that would change the enabled status if they
     * were both used.
     */
    function testRandomCached () {
        // Initially enabled
        $c = array('enabled' => 3, 'bucketing' => 'random');
        $w = new Testing_Feature_MockWorld(array('uaid' => 1, 'random' => 0));
        $config = new Feature_Config('foo', $c, $w);
        $this->assertTrue($config->isEnabled());
        $w->nextRandomValue(.5);
        $this->assertTrue($config->isEnabled());

        // Initially disabled
        $c = array('enabled' => 3, 'bucketing' => 'random');
        $w = new Testing_Feature_MockWorld(array('uaid' => 1, 'random' => .5));
        $config = new Feature_Config('foo', $c, $w);
        $this->assertFalse($config->isEnabled());
        $w->nextRandomValue(0);
        $this->assertFalse($config->isEnabled());
    }

    function testDescription () {
        // Default description.
        $c = array('enabled' => 'on');
        $w = new Testing_Feature_MockWorld(array());
        $config = new Feature_Config('foo', $c, $w);
        $this->assertNotNull($config->description());

        // Provided description.
        $c = array('enabled' => 'on', 'description' => 'The description.');
        $w = new Testing_Feature_MockWorld(array());
        $config = new Feature_Config('foo', $c, $w);
        $this->assertEquals($config->description(), 'The description.');
    }

    function testIsEnabledForAcceptsREST_User() {
        //we don't want to test the implementation of user bucketing here, just the public API
        $user_id = 1;
        $user = $this->getMock('REST_User');
        $user->expects($this->once())
            ->method('getUserId')
            ->will($this->returnValue($user_id));
        $config = new Feature_Config('foo', array('enabled' => 'off'), new Testing_Feature_MockWorld(array()));
        $this->assertFalse($config->isEnabledFor($user));
    }

    function testIsEnabledForAcceptsEtsyModel_User() {
        //we don't want to test the implementation of user bucketing here, just the public API
        $user = new EtsyModel_User();
        $user->user_id = 1;
        $config = new Feature_Config('foo', array('enabled' => 'off'), new Testing_Feature_MockWorld(array()));
        $this->assertFalse($config->isEnabledFor($user));
    }


    ////////////////////////////////////////////////////////////////////////
    // Test helper methods.

    /*
     * Given a config stanza and a world configuration, we expect that
     * isEnabled() will return true and that variant will be a given
     * value (default 'on').
     */
    private function expectEnabled ($stanza, $world, $variant = 'on') {
        $config = new Feature_Config('foo', $stanza, new Testing_Feature_MockWorld($world));
        $this->assertTrue($config->isEnabled());
        $this->assertEquals($config->variant(), $variant);

        if (is_array($stanza) && array_key_exists('enabled', $stanza) && $stanza['enabled'] === 0) {
            unset($stanza['enabled']);
            $this->expectEnabled($stanza, $world, $variant);
        }
    }

    /*
     * Given a config stanza and a world configuration, we expect that
     * isEnabled() will return false.
     */
    private function expectDisabled ($stanza, $world) {
        $config = new Feature_Config('foo', $stanza, new Testing_Feature_MockWorld($world));
        $this->assertFalse($config->isEnabled());
        if (is_array($stanza) && array_key_exists('enabled', $stanza) && $stanza['enabled'] === 0) {
            unset($stanza['enabled']);
            $this->expectDisabled($stanza, $world);
        }
    }
}
