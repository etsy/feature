<?php

namespace CafeMedia\Feature\Tests;

use CafeMedia\Feature\Variant;
use CafeMedia\Feature\Stanza;
use PHPUnit\Framework\TestCase;

class VariantTest extends TestCase
{
    private $variant;

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
                'zipcode',
                'country',
                'region'
            ])
            ->getMock();
        $world->method('configValue')->willReturn(['enabled' => 100]);
        $world->method('userID')->willReturn(5);
        $world->method('uaid')->willReturn('as54gerfd');
        $world->method('isInternalRequest')->willReturn(false);
        $world->method('isAdmin')->willReturn(false);
        $world->method('urlFeatures')->willReturn('feature');
        $world->method('userName')->willReturn('testUserName');
        $world->method('viewingGroup')->willReturn(false);
        $world->method('country')->willReturn('us');
        $world->method('region')->willReturn('ny');
        $world->method('zipcode')->willReturn('12345');
        $this->variant = (new Variant($world))
                             ->addStanza(new Stanza([
                                 'description' => 'description of the stanza',
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
                             ]))
                             ->addBucketingID('123bucketingid321')
                             ->addName('test');
    }

    public function testToString()
    {
        $this->assertEquals((string)$this->variant, 'off');
    }
}