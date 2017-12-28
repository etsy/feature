<?php

declare(strict_types=1);

namespace PabloJoan\Feature\Tests;

use PabloJoan\Feature\Feature;
use PHPUnit\Framework\TestCase;

class FeatureTest extends TestCase
{
    private $feature;

    function setUp ()
    {
        $this->feature = new Feature([
            'features' => [
                'testFeature' => [
                    'description' => 'this is the description',
                    'enabled' => [
                        'test1' => 20,
                        'test2' => 30,
                        'test3' => 15,
                        'test4' => 35
                    ],
                    'users' => ['test1' => '2', 'test4' => '7'],
                    'groups' => ['test1' => 'group1', 'test2' => 'group2'],
                    'sources' => ['test3' => 'source1', 'test4' => 'source2'],
                    'admin' => 'test3',
                    'internal' => 'test1',
                    'public_url_override' => true,
                    'exclude_from' => [
                        'zips' => ['10014', '10023'],
                        'countries' => ['us', 'rd'],
                        'regions' => ['ny', 'nj', 'ca']
                    ],
                    'start' => '20170214',
                    'end' => '99990530'
                ],
                'testFeature2' => ['enabled' => 0, 'bucketing' => 'random']
            ],
            'url' => 'http://www.testurl.com/?feature=testFeature:test3',
            'source' => 'source2',
            'user' => [
                'uaid' => 'as54gerfd',
                'id' => '5',
                'is-admin' => false,
                'group' => 'group3',
                'internal-ip' => false
            ]
        ]);
    }

    function testIsEnabled ()
    {
        $this->assertEquals($this->feature->isEnabled('testFeature'), true);
        $this->assertEquals($this->feature->isEnabled('testFeature2'), false);
    }

    function testIsEnabledFor ()
    {
        $this->assertEquals(
            $this->feature->isEnabledFor(
                'testFeature2',
                [
                    'uaid' => 'as54gerfd',
                    'id' => '5',
                    'is-admin' => false,
                    'group' => 'group',
                    'internal-ip' => false
                ]
            ),
            false
        );

        $this->assertEquals(
            $this->feature->isEnabledFor(
                'testFeature',
                [
                    'uaid' => 'kl23j4n5',
                    'id' => '2',
                    'is-admin' => false,
                    'group' => 'group',
                    'internal-ip' => false
                ]
            ),
            true
        );
    }

    function testIsEnabledBucketingBy ()
    {
        $this->assertEquals(
            $this->feature->isEnabledBucketingBy('testFeature', 'test'),
            true
        );
    }

    function testVariant ()
    {
        $this->assertEquals($this->feature->variant('testFeature'), 'test3');
        $this->assertEquals($this->feature->variant('testFeature2'), '');
    }

    function testVariantFor ()
    {
        $this->assertEquals(
            $this->feature->variantFor(
                'testFeature',
                [
                    'uaid' => 'as54gerfd',
                    'id' => '7',
                    'is-admin' => false,
                    'group' => 'group',
                    'internal-ip' => false
                ]
            ),
            'test3'
        );
    }

    function testVariantBucketingBy ()
    {
        $this->assertEquals(
            $this->feature->variantBucketingBy('testFeature', 'test'),
            'test3'
        );
    }

    function testDescription ()
    {
        $this->assertEquals(
            $this->feature->description('testFeature'),
            'this is the description'
        );
    }

    function testChangeFeatures ()
    {
        $this->feature->changeFeatures([
            'testFeature' => [
                'description' => 'different description',
                'enabled' => [
                    'test1' => 0,
                    'test2' => 0,
                    'test3' => 0,
                    'test4' => 0
                ]
            ],
            'testFeature2' => ['enabled' => 100]
        ]);

        $this->assertEquals($this->feature->isEnabled('testFeature'), false);
        $this->assertEquals($this->feature->isEnabled('testFeature2'), true);

        $this->assertEquals($this->feature->variant('testFeature'), '');
        $this->assertEquals($this->feature->variant('testFeature2'), 'on');

        $this->assertEquals(
            $this->feature->description('testFeature'),
            'different description'
        );
    }

    function testChangeFeature ()
    {
        $this->feature->changeFeature(
            'testFeature2',
            [
                'enabled' => ['test1' => 0, 'test4' => 0],
                'users' => ['test1' => '2', 'test4' => '7'],
                'sources' => ['test1' => 'source3'],
                'public_url_override' => true
            ]
        );
        $this->assertEquals($this->feature->isEnabled('testFeature2'), false);
        $this->assertEquals($this->feature->variant('testFeature2'), '');
    }

    function testChangeUser ()
    {
        $this->feature->changeFeature(
            'testFeature2',
            [
                'enabled' => ['test1' => 0, 'test4' => 0],
                'users' => ['test1' => '2', 'test4' => '7'],
                'sources' => ['test1' => 'source3'],
                'public_url_override' => true
            ]
        );
        $this->feature->changeUser(['id' => '2']);
        $this->assertEquals($this->feature->isEnabled('testFeature2'), true);
        $this->assertEquals($this->feature->variant('testFeature2'), 'test1');
    }

    function testChangeUrl ()
    {
        $this->feature->changeFeature(
            'testFeature2',
            [
                'enabled' => ['test1' => 0, 'test4' => 0],
                'users' => ['test1' => '2', 'test4' => '7'],
                'sources' => ['test1' => 'source3'],
                'public_url_override' => true
            ]
        );
        $this->feature->changeUrl(
            'http://www.testurl.com/?feature=testFeature2:test4'
        );
        $this->assertEquals($this->feature->isEnabled('testFeature2'), true);
        $this->assertEquals($this->feature->variant('testFeature2'), 'test4');
    }

    function testChangeSource ()
    {
        $this->feature->changeFeature(
            'testFeature2',
            [
                'enabled' => ['test1' => 0, 'test4' => 0],
                'users' => ['test1' => '2', 'test4' => '7'],
                'sources' => ['test1' => 'source3'],
                'public_url_override' => true
            ]
        );
        $this->feature->changeSource('source3');
        $this->assertEquals($this->feature->isEnabled('testFeature2'), true);
        $this->assertEquals($this->feature->variant('testFeature2'), 'test1');
    }
}
