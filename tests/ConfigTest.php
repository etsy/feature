<?php

namespace CafeMedia\Feature\Tests;

use CafeMedia\Feature\Config;
use CafeMedia\Feature\User;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    private $config;

    public function setUp()
    {
        $world = $this->getMockBuilder('CafeMedia\Feature\World')
                      ->disableOriginalConstructor()
                      ->setMethods([
                          'configValue',
                          'userID',
                          'uaid',
                          'isInternalRequest',
                          'isAdmin',
                          'urlFeatures',
                          'userName',
                          'viewingGroup',
                          'isSource',
                          'country',
                          'region',
                          'zipcode'
                      ])
                      ->getMock();
        $world->method('configValue')->willReturn([
            'description' => 'this is the description of the stanza',
            'enabled' => [
                'test1' => 20,
                'test2' => 30,
                'test3' => 15,
                'test4' => 35
            ],
            'users' => ['user1', 'user2', 'user3'],
            'groups' => ['group1', 'group2', 'group3'],
            'sources' => ['source1', 'source2', 'source3'],
            'admin' => 'test3',
            'internal' => 'test1',
            'public_url_override' => true,
            'bucketing' => 'random',
            'exclude_from' => [
                'zips' => [10014, 10023],
                'countries' => ['us', 'rd'],
                'regions' => ['ny', 'nj', 'ca']
            ],
            'start' => 20170314,
            'end' => 20170530
        ]);
        $world->method('userID')->willReturn(5);
        $world->method('uaid')->willReturn('as54gerfd');
        $world->method('isInternalRequest')->willReturn(false);
        $world->method('isAdmin')->willReturn(false);
        $world->method('urlFeatures')->willReturn('feature');
        $world->method('userName')->willReturn('testUserName');
        $world->method('viewingGroup')->willReturn(false);
        $world->method('isSource')->willReturn(false);
        $world->method('country')->willReturn('us');
        $world->method('region')->willReturn('ny');
        $world->method('zipcode')->willReturn('12345');
        $this->config = (new Config($world))->addName('testFeature');
        $this->assertEquals($this->config instanceof Config, true);
    }

    public function testIsEnabled()
    {
        $this->assertEquals($this->config->isEnabled('testFeature'), false);
    }

    public function testVariant()
    {
        $this->assertEquals($this->config->variant(), 'off');
    }

    public function testIsEnabledFor()
    {
        $this->assertEquals(
            $this->config->isEnabledFor(new User([
                'user-uaid' => 'as54gerfd',
                'user-id' => 5,
                'user-name' => 'testUserName',
                'is-admin' => false,
                'user-group' => 'group',
                'internal-ip' => false
            ])),
            false
        );
    }

    public function testIsEnabledBucketingBy()
    {
        $this->assertEquals($this->config->isEnabledBucketingBy('test'), false);
    }

    public function testVariantFor()
    {
        $this->assertEquals(
            $this->config->variantFor(new User([
                'user-uaid' => 'as54gerfd',
                'user-id' => 5,
                'user-name' => 'testUserName',
                'is-admin' => false,
                'user-group' => 'group',
                'internal-ip' => false
            ])),
            'off'
        );
    }

    public function testVariantBucketingBy()
    {
        $this->assertEquals(
            $this->config->variantBucketingBy('test', 'test'),
            'off'
        );
    }

    public function testDescription()
    {
        $this->assertEquals(
            $this->config->description('test'),
            'this is the description of the stanza'
        );
    }
}
