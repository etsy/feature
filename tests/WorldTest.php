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

    public function testConfigValue()
    {
        $this->assertEquals($this->world->configValue('test'), null);
    }

    public function testUaid()
    {
        $this->assertEquals($this->world->uaid(), '');
    }

    public function testUserId()
    {
        $this->assertEquals($this->world->userId(), '');
    }

    public function testUserName()
    {
        $this->assertEquals($this->world->userName(), '');
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

    public function testInGroup()
    {
        $this->assertEquals($this->world->inGroup(), false);
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
        $this->assertEquals($this->world->urlFeatures(), '');
    }

    public function testRandom()
    {
        $this->assertEquals(is_numeric($this->world->random()), true);
    }

    public function testHash()
    {
        $this->assertEquals($this->world->hash('test'), 0.91731063090264797);
    }

    public function testSelections()
    {
        $this->world->log('test', 'test', 'test');
        $this->assertEquals($this->world->selections(), array(array('test', 'test', 'test')));
    }
}
