<?php

namespace CafeMedia\Feature\Tests;

use CafeMedia\Feature\Config;

/**
 * Class ConfigTest
 * @package CafeMedia\Feature\Tests
 */
class ConfigTest extends \PHPUnit_Framework_TestCase
{
    private $config;

    public function setUp()
    {
        $this->config = new Config(
            'test',
            'test',
            $this->getMockBuilder('CafeMedia\Feature\World')->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder('CafeMedia\Feature\Logger')->disableOriginalConstructor()->getMock()
        );
    }

    /**
     * @covers \CafeMedia\Feature\Config::isEnabled
     */
    public function testIsEnabled()
    {
        $this->assertEquals($this->config->isEnabled('test'), true);
    }

    /**
     * @covers \CafeMedia\Feature\Config::variant
     */
    public function testVariant()
    {
        $this->assertEquals($this->config->variant(), 'test');
    }

    /**
     * @covers \CafeMedia\Feature\Config::isEnabledFor
     */
    public function testIsEnabledFor()
    {
        $this->assertEquals($this->config->isEnabledFor((object) array('user_id' => 1)), true);
    }

    /**
     * @covers \CafeMedia\Feature\Config::isEnabledBucketingBy
     */
    public function testIsEnabledBucketingBy()
    {
        $this->assertEquals($this->config->isEnabledBucketingBy('test'), true);
    }

    /**
     * @covers \CafeMedia\Feature\Config::variantFor
     */
    public function testVariantFor()
    {
        $this->assertEquals($this->config->variantFor((object) array('user_id' => 1)), 'test');
    }

    /**
     * @covers \CafeMedia\Feature\Config::variantBucketingBy
     */
    public function testVariantBucketingBy()
    {
        $this->assertEquals($this->config->variantBucketingBy('test', 'test'), 'test');
    }

    /**
     * @covers \CafeMedia\Feature\Config::description
     */
    public function testDescription()
    {
        $this->assertEquals($this->config->description('test'), 'No description.');
    }

    public function testConstants()
    {
        $this->assertEquals(
            array(
                Config::DESCRIPTION,
                Config::ENABLED,
                Config::USERS,
                Config::GROUPS,
                Config::SOURCES,
                Config::ADMIN,
                Config::INTERNAL,
                Config::PUBLIC_URL_OVERRIDE,
                Config::BUCKETING,
                Config::ON,
                Config::OFF,
                Config::UAID,
                Config::USER,
                Config::RANDOM
            ),
            array(
                'description',
                'enabled',
                'users',
                'groups',
                'sources',
                'admin',
                'internal',
                'public_url_override',
                'bucketing',
                'on',
                'off',
                'uaid',
                'user',
                'random'
            )
        );
    }
}
