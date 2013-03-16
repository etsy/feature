<?php

/**
 * @group dbunit
 * @medium
 */
class Feature_WorldTest extends PHPUnit_Extensions_MultipleDatabase_TestCase {
    private $uaid;
    private $world;
    private $user_id;

    function setUp() {
        parent::setUp();
        UAIDCookie::resetState();
        UAIDCookie::setUpUAID();
        $this->uaid = UAIDCookie::getSecureCookie();
        $this->assertNotNull($this->uaid);

        $logger = $this->getMock('Logger', array('log'));
        $this->world = new Feature_World($logger);
        $this->user_id = 991;

        $this->setLoggedUserId(null);
        $this->assertNull(Std::loggedUser());
    }

    function testIsAdminWithBlankUAIDCookie() {
        $this->setLoggedUserId($this->user_id);

        $this->assertFalse($this->world->isAdmin($this->user_id));
    }

    function testIsAdminWithValidNonAdminUserUAIDCookie() {
        $this->setLoggedUserId($this->user_id);
        $this->uaid->set(UAIDCookie::USER_ID_ATTRIBUTE, $this->user_id);

        $this->assertFalse($this->world->isAdmin($this->user_id));
    }

    function testIsAdminWithValidAdminUAIDCookie() {
        $this->setLoggedUserId($this->user_id);
        $this->uaid->set(UAIDCookie::USER_ID_ATTRIBUTE, $this->user_id);
        $this->uaid->set(UAIDCookie::ADMIN_ATTRIBUTE, '1');

        $this->assertTrue($this->world->isAdmin($this->user_id));
    }

    function testIsAdminWithNonLoggedInAdminAndValidAdminUAIDCookie() {
        $this->setLoggedUserId(null);
        $this->uaid->set(UAIDCookie::USER_ID_ATTRIBUTE, $this->user_id);
        $this->uaid->set(UAIDCookie::ADMIN_ATTRIBUTE, '1');

        $this->assertFalse($this->world->isAdmin($this->user_id));
    }

    function testIsAdminWithLoggedInAdminUserAndBlankUAIDCookie() {
        $user = $this->adminUser();
        $this->setLoggedUserId($user->user_id);

        $this->assertTrue($this->world->isAdmin($user->user_id));
    }

    function testIsAdminWithLoggedInNonAdminUserAndBlankUAIDCookie() {
        $user = $this->nonAdminUser();
        $this->setLoggedUserId($user->user_id);

        $this->assertFalse($this->world->isAdmin($user->user_id));
    }

    function testIsAdminWithNonLoggedInAdminUserAndBlankUAIDCookie() {
        $user = $this->adminUser();
        $this->setLoggedUserId(null);

        $this->assertTrue($this->world->isAdmin($user->user_id));
    }

    function testIsAdminWithNonLoggedInNonAdminUserAndBlankUAIDCookie() {
        $user = $this->nonAdminUser();
        $this->setLoggedUserId(null);

        $this->assertFalse($this->world->isAdmin($user->user_id));
    }

    function testAtlasWorld() {
        $user = $this->atlasUser();
        $this->setLoggedUserId($user->id);
        $this->setAtlasRequest(true);

        $this->assertFalse($this->world->isAdmin($user->id));
        $this->assertFalse($this->world->inGroup($user->id, 1));
        $this->assertEquals($user->id, $this->world->userID());

        $this->setAtlasRequest(false);
    }

    function testHash() {
        $this->assertInternalType('float', $this->world->hash('somevalue'));

        $this->assertEquals(
            $this->world->hash('somevalue'),
            $this->world->hash('somevalue'),
            'ensure return value is consistent'
        );

        $this->assertGreaterThanOrEqual(0, $this->world->hash('somevalue'));
        $this->assertLessThan(1, $this->world->hash('somevalue'));
    }

    protected function getDatabaseConfigs() {
        $index_yml = dirname(__FILE__) . '/data/world/etsy_index.yml';
        if (!file_exists($index_yml)) {
            throw new Exception($index_yml . ' does not exist');
        }
        $builder = new PHPUnit_Extensions_MultipleDatabase_DatabaseConfig_Builder();
        $etsy_index = $builder
            ->connection(Testing_EtsyORM_Connections::ETSY_INDEX())
            ->dataSet(new PHPUnit_Extensions_Database_DataSet_YamlDataSet($index_yml))
            ->build();

        $aux_yml = dirname(__FILE__) . '/data/world/etsy_aux.yml';
        if (!file_exists($aux_yml)) {
            throw new Exception($aux_yml . ' does not exist');
        }
        $builder = new PHPUnit_Extensions_MultipleDatabase_DatabaseConfig_Builder();
        $etsy_aux = $builder
            ->connection(Testing_EtsyORM_Connections::ETSY_AUX())
            ->dataSet(new PHPUnit_Extensions_Database_DataSet_YamlDataSet($aux_yml))
            ->build();

        return array($etsy_index, $etsy_aux);
    }

    private function nonAdminUser() {
        return EtsyORM::getFinder('User')->find(1);
    }

    private function adminUser() {
        return EtsyORM::getFinder('User')->find(2);
    }

    private function atlasUser() {
        return EtsyORM::getFinder('Staff')->find(3);
    }

    private function setAtlasRequest($is_atlas) {
        $_SERVER["atlas_request"] = $is_atlas ? 1 : 0;
    }

    private function setLoggedUserId($user_id) {
        //Std::loggedUser() uses this global
        $GLOBALS['cookie_user_id'] = $user_id;
    }
}
