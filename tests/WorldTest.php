<?php

namespace CafeMedia\Feature\Tests;

use CafeMedia\Feature\World;
use CafeMedia\Feature\User;
use PHPUnit\Framework\TestCase;

class WorldTest extends TestCase
{
    private $world;

    public function setUp()
    {
        $this->world = (new World(['test' => ['value']]))
                           ->addUrl('feature')
                           ->addSource('')
                           ->addUser(new User([
                               'user-uaid' => 'as54gerfd',
                               'user-id' => 5,
                               'user-name' => 'testUserName',
                               'is-admin' => false,
                               'user-group' => 'group',
                               'internal-ip' => false,
                               'zipcode' => 10203,
                               'region' => 'ny',
                               'country' => 'us'
                           ]));
        $this->assertEquals($this->world instanceof World, true);
    }

    public function testConfigValue()
    {
        $this->assertEquals($this->world->configValue('test'), ['value']);
    }

    public function testUaid()
    {
        $this->assertEquals($this->world->uaid(), 'as54gerfd');
    }

    public function testUserId()
    {
        $this->assertEquals($this->world->userId(), 5);
    }

    public function testUserName()
    {
        $this->assertEquals($this->world->userName(), 'testUserName');
    }

    public function testViewingGroup()
    {
        $this->assertEquals($this->world->viewingGroup('test'), false);
    }

    public function testIsSource()
    {
        $this->assertEquals($this->world->isSource('test'), false);
        $this->assertEquals($this->world->isSource(''), true);
    }

    public function testIsAdmin()
    {
        $this->assertEquals($this->world->isAdmin(), false);
    }

    public function testIsInternalRequest()
    {
        $this->assertEquals($this->world->isInternalRequest(), false);
    }

    public function testUrlFeatures()
    {
        $this->assertEquals($this->world->urlFeatures(), 'feature');
    }

    public function testZipcode()
    {
        $this->assertEquals($this->world->zipcode(), 10203);
    }

    public function testRegion()
    {
        $this->assertEquals($this->world->region(), 'ny');
    }

    public function testCounrty()
    {
        $this->assertEquals($this->world->country(), 'us');
    }
}