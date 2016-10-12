<?php

namespace CafeMedia\Feature\Tests;

use CafeMedia\Feature\World;

/**
 * Class WorldTest
 * @package CafeMedia\Feature\Tests
 */
class WorldTest extends \PHPUnit_Framework_TestCase
{
    private $world;

    public function setUp()
    {
        $this->world = new World(
            $this->getMockBuilder('CafeMedia\Feature\Logger')->disableOriginalConstructor()->getMock()
        );
    }
    
    /**
     * @covers \CafeMedia\Feature\World::configValue
     */
    public function testConfigValue()
    {
        $this->assertEquals($this->world->configValue('test'), null);
    }
    
    /**
     * @covers \CafeMedia\Feature\World::uaid
     */
    public function testUaid()
    {
        $this->assertEquals($this->world->uaid(), '');
    }
    
    /**
     * @covers \CafeMedia\Feature\World::userId
     */
    public function testUserId()
    {
        $this->assertEquals($this->world->userId(), '');
    }
    
    /**
     * @covers \CafeMedia\Feature\World::userName
     */
    public function testUserName()
    {
        $this->assertEquals($this->world->userName(), '');
    }
    
    /**
     * @covers \CafeMedia\Feature\World::viewingGroup
     */
    public function testViewingGroup()
    {
        $this->assertEquals($this->world->viewingGroup('test'), false);
    }

    /**
     * @covers \CafeMedia\Feature\World::isSource
     */
    public function testIsSource()
    {
        $this->assertEquals($this->world->isSource('test'), false);
        $this->assertEquals($this->world->isSource(''), true);
    }

    /**
     * @covers \CafeMedia\Feature\World::inGroup
     */
    public function testInGroup()
    {
        $this->assertEquals($this->world->inGroup(), false);
    }

    /**
     * @covers \CafeMedia\Feature\World::isAdmin
     */
    public function testIsAdmin()
    {
        $this->assertEquals($this->world->isAdmin(), false);
    }

    /**
     * @covers \CafeMedia\Feature\World::isInternalRequest
     */
    public function testIsInternalRequest()
    {
        $this->assertEquals($this->world->isInternalRequest(), false);
    }

    /**
     * @covers \CafeMedia\Feature\World::urlFeatures
     */
    public function testUrlFeatures()
    {
        $this->assertEquals($this->world->urlFeatures(), '');
    }

    /**
     * @covers \CafeMedia\Feature\World::random
     */
    public function testRandom()
    {
        $this->assertEquals(is_numeric($this->world->random()), true);
    }

    /**
     * @covers \CafeMedia\Feature\World::hash
     */
    public function testHash()
    {
        $this->assertEquals($this->world->hash('test'), 0.91731063090264797);
    }

    /**
     * @covers \CafeMedia\Feature\World::selections
     */
    public function testSelections()
    {
        $this->world->log('test', 'test', 'test');
        $this->assertEquals($this->world->selections(), array(array('test', 'test', 'test')));
    }
}
