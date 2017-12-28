<?php

declare(strict_types=1);

namespace PabloJoan\Feature\Tests;

use PabloJoan\Feature\Feature;
use PHPUnit\Framework\TestCase;

class ApiTest extends TestCase
{
    function testFeature ()
    {
        $feature = new Feature([
            'features' => [
                'testFeature' => ['enabled' => 100],
                'testFeature2' => ['enabled' => 0]
            ]
        ]);

        $this->assertEquals($feature->isEnabled('testFeature'), true);
        $this->assertEquals($feature->isEnabled('testFeature2'), false);

        $this->assertEquals($feature->isEnabledFor('testFeature', []), true);
        $this->assertEquals($feature->isEnabledFor('testFeature2', []), false);

        $this->assertEquals(
            $feature->isEnabledBucketingBy('testFeature', 'testid1'),
            true
        );
        $this->assertEquals(
            $feature->isEnabledBucketingBy('testFeature2', 'testid2'),
            false
        );

        $this->assertEquals($feature->variant('testFeature'), 'on');
        $this->assertEquals($feature->variant('testFeature2'), '');

        $this->assertEquals($feature->variantFor('testFeature', []), 'on');
        $this->assertEquals($feature->variantFor('testFeature2', []), '');

        $this->assertEquals(
            $feature->variantBucketingBy('testFeature', 'testid1'),
            'on'
        );
        $this->assertEquals(
            $feature->variantBucketingBy('testFeature2', 'testid2'),
            ''
        );

        $feature->changeFeatures([
            'testFeature' => ['enabled' => 0],
            'testFeature2' => ['enabled' => 100]
        ]);

        $this->assertEquals($feature->isEnabled('testFeature'), false);
        $this->assertEquals($feature->isEnabled('testFeature2'), true);

        $this->assertEquals($feature->isEnabledFor('testFeature', []), false);
        $this->assertEquals($feature->isEnabledFor('testFeature2', []), true);

        $this->assertEquals(
            $feature->isEnabledBucketingBy('testFeature', 'testid1'),
            false
        );
        $this->assertEquals(
            $feature->isEnabledBucketingBy('testFeature2', 'testid2'),
            true
        );

        $this->assertEquals($feature->variant('testFeature'), '');
        $this->assertEquals($feature->variant('testFeature2'), 'on');

        $this->assertEquals($feature->variantFor('testFeature', []), '');
        $this->assertEquals($feature->variantFor('testFeature2', []), 'on');

        $this->assertEquals(
            $feature->variantBucketingBy('testFeature', 'testid1'),
            ''
        );
        $this->assertEquals(
            $feature->variantBucketingBy('testFeature2', 'testid2'),
            'on'
        );
        
        $feature->changeFeature('testFeature2', ['enabled' => 0]);

        $this->assertEquals($feature->isEnabled('testFeature'), false);
        $this->assertEquals($feature->isEnabled('testFeature2'), false);

        $this->assertEquals($feature->isEnabledFor('testFeature', []), false);
        $this->assertEquals($feature->isEnabledFor('testFeature2', []), false);

        $this->assertEquals(
            $feature->isEnabledBucketingBy('testFeature', 'testid1'),
            false
        );
        $this->assertEquals(
            $feature->isEnabledBucketingBy('testFeature2', 'testid2'),
            false
        );

        $this->assertEquals($feature->variant('testFeature'), '');
        $this->assertEquals($feature->variant('testFeature2'), '');

        $this->assertEquals($feature->variantFor('testFeature', []), '');
        $this->assertEquals($feature->variantFor('testFeature2', []), '');

        $this->assertEquals(
            $feature->variantBucketingBy('testFeature', 'testid1'),
            ''
        );
        $this->assertEquals(
            $feature->variantBucketingBy('testFeature2', 'testid2'),
            ''
        );
    }

    function testDescription ()
    {
        $feature = new Feature([
            'features' => [
                'testFeature' => [
                    'enabled' => 100,
                    'description' => 'testFeature'
                ],
                'testFeature2' => [
                    'enabled' => 0,
                    'description' => 'testFeature2'
                ]
            ]
        ]);
        $this->assertEquals(
            $feature->description('testFeature'),
            'testFeature'
        );
        $this->assertEquals(
            $feature->description('testFeature2'),
            'testFeature2'
        );
    }

    function testVariant ()
    {
        $feature = new Feature([
            'features' => [
                'testFeature' => [
                    'enabled' => ['variant1' => 50, 'variant2' => 50],
                ],
                'testFeature2' => [
                    'enabled' => ['variant3' => 25, 'variant4' => 25],
                ]
            ]
        ]);

        $this->assertEquals($feature->isEnabled('testFeature'), true);
        $this->assertEquals($feature->isEnabledFor('testFeature', []), true);
        $this->assertEquals(
            $feature->isEnabledBucketingBy('testFeature', 'testid1'),
            true
        );

        $variant = in_array(
            $feature->variant('testFeature'),
            ['variant1', 'variant2'],
            true
        );
        $this->assertEquals($variant, true);
        $variant = in_array(
            $feature->variant('testFeature2'),
            ['variant3', 'variant4', ''],
            true
        );
        $this->assertEquals($variant, true);

        $variant = in_array(
            $feature->variantFor('testFeature', []),
            ['variant1', 'variant2'],
            true
        );
        $this->assertEquals($variant, true);
        $variant = in_array(
            $feature->variantFor('testFeature2', []),
            ['variant3', 'variant4', ''],
            true
        );
        $this->assertEquals($variant, true);

        $variant = in_array(
            $feature->variantBucketingBy('testFeature', 'testid1'),
            ['variant1', 'variant2'],
            true
        );
        $this->assertEquals($variant, true);
        $variant = in_array(
            $feature->variantBucketingBy('testFeature2', 'testid2'),
            ['variant3', 'variant4', ''],
            true
        );
        $this->assertEquals($variant, true);
    }

    function testUsers ()
    {
        $feature = new Feature([
            'features' => [
                'testFeature' => [
                    'enabled' => 0,
                    'users' => ['test1' => '2', 'test4' => ['7', '8', '9']],
                ],
                'testFeature2' => [
                    'enabled' => ['variant1' => 25, 'variant2' => 25],
                    'users' => ['variant2' => '5']
                ]
            ],
            'user' => ['id' => '5']
        ]);

        $this->assertEquals($feature->isEnabled('testFeature'), false);
        $this->assertEquals($feature->isEnabled('testFeature2'), true);

        $this->assertEquals($feature->variant('testFeature'), '');
        $this->assertEquals($feature->variant('testFeature2'), 'variant2');

        $feature->changeUser(['id' => '7']);
        $this->assertEquals($feature->isEnabled('testFeature'), true);
        $this->assertEquals($feature->variant('testFeature'), 'test4');

        $this->assertEquals($feature->isEnabledFor('testFeature', []), false);
        $this->assertEquals(
            $feature->isEnabledFor('testFeature', ['id' => '9']),
            true
        );
        $this->assertEquals(
            $feature->isEnabledFor('testFeature', ['id' => '8']),
            true
        );
        $this->assertEquals(
            $feature->isEnabledFor('testFeature', ['id' => '7']),
            true
        );
        $this->assertEquals(
            $feature->isEnabledFor('testFeature', ['id' => '2']),
            true
        );
        $this->assertEquals(
            $feature->isEnabledFor('testFeature', ['id' => '5']),
            false
        );
        $this->assertEquals(
            $feature->isEnabledFor('testFeature2', ['id' => '5']),
            true
        );

        $this->assertEquals($feature->variantFor('testFeature', []), '');
        $this->assertEquals(
            $feature->variantFor('testFeature', ['id' => '9']),
            'test4'
        );
        $this->assertEquals(
            $feature->variantFor('testFeature', ['id' => '8']),
            'test4'
        );
        $this->assertEquals(
            $feature->variantFor('testFeature', ['id' => '7']),
            'test4'
        );
        $this->assertEquals(
            $feature->variantFor('testFeature', ['id' => '2']),
            'test1'
        );
        $this->assertEquals(
            $feature->variantFor('testFeature', ['id' => '5']),
            ''
        );
        $this->assertEquals(
            $feature->variantFor('testFeature2', ['id' => '5']),
            'variant2'
        );
    }

    function testGroups ()
    {
        $feature = new Feature([
            'features' => [
                'testFeature' => [
                    'enabled' => 0,
                    'groups' => ['test1' => '2', 'test4' => ['7', '8', '9']],
                ],
                'testFeature2' => [
                    'enabled' => ['variant1' => 25, 'variant2' => 25],
                    'groups' => ['variant2' => '5']
                ]
            ],
            'user' => ['group' => '5']
        ]);

        $this->assertEquals($feature->isEnabled('testFeature'), false);
        $this->assertEquals($feature->isEnabled('testFeature2'), true);

        $this->assertEquals($feature->variant('testFeature'), '');
        $this->assertEquals($feature->variant('testFeature2'), 'variant2');

        $feature->changeUser(['group' => '7']);
        $this->assertEquals($feature->isEnabled('testFeature'), true);
        $this->assertEquals($feature->variant('testFeature'), 'test4');

        $this->assertEquals($feature->isEnabledFor('testFeature', []), false);
        $this->assertEquals(
            $feature->isEnabledFor('testFeature', ['group' => '9']),
            true
        );
        $this->assertEquals(
            $feature->isEnabledFor('testFeature', ['group' => '8']),
            true
        );
        $this->assertEquals(
            $feature->isEnabledFor('testFeature', ['group' => '7']),
            true
        );
        $this->assertEquals(
            $feature->isEnabledFor('testFeature', ['group' => '2']),
            true
        );
        $this->assertEquals(
            $feature->isEnabledFor('testFeature', ['group' => '5']),
            false
        );
        $this->assertEquals(
            $feature->isEnabledFor('testFeature2', ['group' => '5']),
            true
        );

        $this->assertEquals($feature->variantFor('testFeature', []), '');
        $this->assertEquals(
            $feature->variantFor('testFeature', ['group' => '9']),
            'test4'
        );
        $this->assertEquals(
            $feature->variantFor('testFeature', ['group' => '8']),
            'test4'
        );
        $this->assertEquals(
            $feature->variantFor('testFeature', ['group' => '7']),
            'test4'
        );
        $this->assertEquals(
            $feature->variantFor('testFeature', ['group' => '2']),
            'test1'
        );
        $this->assertEquals(
            $feature->variantFor('testFeature', ['group' => '5']),
            ''
        );
        $this->assertEquals(
            $feature->variantFor('testFeature2', ['group' => '5']),
            'variant2'
        );
    }

    function testSources ()
    {
        $feature = new Feature([
            'features' => [
                'testFeature' => [
                    'enabled' => 0,
                    'sources' => ['on' => 'test', 'off' => 'test2'],
                ],
                'testFeature2' => [
                    'enabled' => 100,
                    'sources' => ['off' => 'test', 'on' => 'test2']
                ]
            ],
            'source' => 'test'
        ]);

        $this->assertEquals($feature->isEnabled('testFeature'), true);
        $this->assertEquals($feature->isEnabled('testFeature2'), false);

        $this->assertEquals($feature->isEnabledFor('testFeature', []), true);
        $this->assertEquals($feature->isEnabledFor('testFeature2', []), false);

        $this->assertEquals(
            $feature->isEnabledBucketingBy('testFeature', 'testid1'),
            true
        );
        $this->assertEquals(
            $feature->isEnabledBucketingBy('testFeature2', 'testid2'),
            false
        );

        $this->assertEquals($feature->variant('testFeature'), 'on');
        $this->assertEquals($feature->variant('testFeature2'), '');

        $this->assertEquals($feature->variantFor('testFeature', []), 'on');
        $this->assertEquals($feature->variantFor('testFeature2', []), '');

        $this->assertEquals(
            $feature->variantBucketingBy('testFeature', 'testid1'),
            'on'
        );
        $this->assertEquals(
            $feature->variantBucketingBy('testFeature2', 'testid2'),
            ''
        );

        $feature->changeSource('test2');

        $this->assertEquals($feature->isEnabled('testFeature'), false);
        $this->assertEquals($feature->isEnabled('testFeature2'), true);

        $this->assertEquals($feature->isEnabledFor('testFeature', []), false);
        $this->assertEquals($feature->isEnabledFor('testFeature2', []), true);

        $this->assertEquals(
            $feature->isEnabledBucketingBy('testFeature', 'testid1'),
            false
        );
        $this->assertEquals(
            $feature->isEnabledBucketingBy('testFeature2', 'testid2'),
            true
        );

        $this->assertEquals($feature->variant('testFeature'), '');
        $this->assertEquals($feature->variant('testFeature2'), 'on');

        $this->assertEquals($feature->variantFor('testFeature', []), '');
        $this->assertEquals($feature->variantFor('testFeature2', []), 'on');

        $this->assertEquals(
            $feature->variantBucketingBy('testFeature', 'testid1'),
            ''
        );
        $this->assertEquals(
            $feature->variantBucketingBy('testFeature2', 'testid2'),
            'on'
        );
    }

    function testAdmin ()
    {
        $feature = new Feature([
            'features' => [
                'testFeature' => ['enabled' => 0, 'admin' => 'on'],
                'testFeature2' => [
                    'enabled' => ['test1' => 100, 'test2' => 0],
                    'admin' => 'test2'
                ]
            ],
            'user' => ['is-admin' => true]
        ]);

        $this->assertEquals($feature->isEnabled('testFeature'), true);
        $this->assertEquals($feature->isEnabled('testFeature2'), true);

        $this->assertEquals($feature->variant('testFeature'), 'on');
        $this->assertEquals($feature->variant('testFeature2'), 'test2');

        $this->assertEquals($feature->isEnabledFor('testFeature', []), false);
        $this->assertEquals($feature->variantFor('testFeature', []), '');
        $this->assertEquals($feature->isEnabledFor('testFeature2', []), true);
        $this->assertEquals($feature->variantFor('testFeature2', []), 'test1');
    }

    function testInternal ()
    {
        $feature = new Feature([
            'features' => [
                'testFeature' => ['enabled' => 0, 'internal' => 'on'],
                'testFeature2' => [
                    'enabled' => ['test1' => 100, 'test2' => 0],
                    'internal' => 'test2'
                ]
            ],
            'user' => ['internal-ip' => true]
        ]);

        $this->assertEquals($feature->isEnabled('testFeature'), true);
        $this->assertEquals($feature->isEnabled('testFeature2'), true);

        $this->assertEquals($feature->variant('testFeature'), 'on');
        $this->assertEquals($feature->variant('testFeature2'), 'test2');

        $this->assertEquals($feature->isEnabledFor('testFeature', []), false);
        $this->assertEquals($feature->variantFor('testFeature', []), '');
        $this->assertEquals($feature->isEnabledFor('testFeature2', []), true);
        $this->assertEquals($feature->variantFor('testFeature2', []), 'test1');
    }

    function testStart ()
    {
        $feature = new Feature([
            'features' => [
                'testFeature' => ['enabled' => 100, 'start' => 'today'],
                'testFeature2' => ['enabled' => 100, 'start' => 'tomorrow']
            ]
        ]);

        $this->assertEquals($feature->isEnabled('testFeature'), true);
        $this->assertEquals($feature->isEnabled('testFeature2'), false);

        $this->assertEquals($feature->isEnabledFor('testFeature', []), true);
        $this->assertEquals($feature->isEnabledFor('testFeature2', []), false);

        $this->assertEquals(
            $feature->isEnabledBucketingBy('testFeature', 'testid1'),
            true
        );
        $this->assertEquals(
            $feature->isEnabledBucketingBy('testFeature2', 'testid2'),
            false
        );

        $this->assertEquals($feature->variant('testFeature'), 'on');
        $this->assertEquals($feature->variant('testFeature2'), '');

        $this->assertEquals($feature->variantFor('testFeature', []), 'on');
        $this->assertEquals($feature->variantFor('testFeature2', []), '');

        $this->assertEquals(
            $feature->variantBucketingBy('testFeature', 'testid1'),
            'on'
        );
        $this->assertEquals(
            $feature->variantBucketingBy('testFeature2', 'testid2'),
            ''
        );
    }

    function testEnd ()
    {
        $feature = new Feature([
            'features' => [
                'testFeature' => ['enabled' => 100, 'end' => 'tomorrow'],
                'testFeature2' => ['enabled' => 100, 'end' => 'yesterday']
            ]
        ]);

        $this->assertEquals($feature->isEnabled('testFeature'), true);
        $this->assertEquals($feature->isEnabled('testFeature2'), false);

        $this->assertEquals($feature->isEnabledFor('testFeature', []), true);
        $this->assertEquals($feature->isEnabledFor('testFeature2', []), false);

        $this->assertEquals(
            $feature->isEnabledBucketingBy('testFeature', 'testid1'),
            true
        );
        $this->assertEquals(
            $feature->isEnabledBucketingBy('testFeature2', 'testid2'),
            false
        );

        $this->assertEquals($feature->variant('testFeature'), 'on');
        $this->assertEquals($feature->variant('testFeature2'), '');

        $this->assertEquals($feature->variantFor('testFeature', []), 'on');
        $this->assertEquals($feature->variantFor('testFeature2', []), '');

        $this->assertEquals(
            $feature->variantBucketingBy('testFeature', 'testid1'),
            'on'
        );
        $this->assertEquals(
            $feature->variantBucketingBy('testFeature2', 'testid2'),
            ''
        );
    }

    function testExcludeFrom ()
    {
        $feature = new Feature([
            'features' => [
                'testFeature' => [
                    'enabled' => 100,
                    'exclude_from' => ['zips' => ['10014', '10023']],
                ],
                'testFeature2' => [
                    'enabled' => 100,
                    'exclude_from' => ['countries' => ['us', 'rd']],
                ],
                'testFeature3' => [
                    'enabled' => 100,
                    'exclude_from' => ['regions' => ['ny', 'nj', 'ca']],
                ]
            ],
            'user' => [
                'country' => 'us',
                'zipcode' => '10014',
                'region' => 'ny'
            ]
        ]);

        $this->assertEquals($feature->isEnabled('testFeature'), false);
        $this->assertEquals($feature->isEnabled('testFeature2'), false);
        $this->assertEquals($feature->isEnabled('testFeature3'), false);

        $this->assertEquals($feature->isEnabledFor('testFeature', []), true);
        $this->assertEquals($feature->isEnabledFor('testFeature2', []), true);
        $this->assertEquals($feature->isEnabledFor('testFeature3', []), true);

        $this->assertEquals(
            $feature->isEnabledBucketingBy('testFeature', 'testid1'),
            false
        );
        $this->assertEquals(
            $feature->isEnabledBucketingBy('testFeature2', 'testid2'),
            false
        );
        $this->assertEquals(
            $feature->isEnabledBucketingBy('testFeature2', 'testid3'),
            false
        );

        $this->assertEquals($feature->variant('testFeature'), '');
        $this->assertEquals($feature->variant('testFeature2'), '');
        $this->assertEquals($feature->variant('testFeature3'), '');

        $this->assertEquals($feature->variantFor('testFeature', []), 'on');
        $this->assertEquals($feature->variantFor('testFeature2', []), 'on');
        $this->assertEquals($feature->variantFor('testFeature2', []), 'on');

        $this->assertEquals(
            $feature->variantBucketingBy('testFeature', 'testid1'),
            ''
        );
        $this->assertEquals(
            $feature->variantBucketingBy('testFeature2', 'testid2'),
            ''
        );
        $this->assertEquals(
            $feature->variantBucketingBy('testFeature2', 'testid3'),
            ''
        );
    }

    function testPublicUrlOverride ()
    {
        $feature = new Feature([
            'features' => [
                'testFeature' => [
                    'enabled' => ['variant1' => 0, 'variant2' => 0],
                    'public_url_override' => true
                ],
                'testFeature2' => [
                    'enabled' => ['variant3' => 0, 'variant4' => 0],
                    'public_url_override' => true
                ]
            ],
            'url' => 'http://www.testurl.com/?feature=testFeature:variant1,testFeature2:variant4'
        ]);

        $this->assertEquals($feature->isEnabled('testFeature'), true);
        $this->assertEquals($feature->isEnabled('testFeature2'), true);

        $this->assertEquals($feature->variant('testFeature'), 'variant1');
        $this->assertEquals($feature->variant('testFeature2'), 'variant4');

        $feature->changeUrl(
            'http://www.testurl.com/?feature=testFeature:variant2,testFeature2:variant3'
        );

        $this->assertEquals($feature->isEnabled('testFeature'), true);
        $this->assertEquals($feature->isEnabled('testFeature2'), true);

        $this->assertEquals($feature->variant('testFeature'), 'variant2');
        $this->assertEquals($feature->variant('testFeature2'), 'variant3');

        $feature->changeUrl('http://www.testurl.com/');

        $this->assertEquals($feature->isEnabled('testFeature'), false);
        $this->assertEquals($feature->isEnabled('testFeature2'), false);

        $this->assertEquals($feature->variant('testFeature'), '');
        $this->assertEquals($feature->variant('testFeature2'), '');
    }

    function testBucketing ()
    {
        $feature = new Feature([
            'features' => [
                'testFeature' => [
                    'enabled' => ['variant1' => 50, 'variant2' => 50],
                    'bucketing' => 'random'
                ],
                'testFeature2' => [
                    'enabled' => ['variant3' => 50, 'variant4' => 50],
                    'bucketing' => 'uaid'
                ],
                'testFeature3' => [
                    'enabled' => ['variant5' => 50, 'variant6' => 50],
                    'bucketing' => 'user'
                ]
            ],
            'user' => ['id' => 'testid5', 'uaid' => 'randomteststring']
        ]);

        $variant = in_array(
            $feature->variant('testFeature'),
            ['variant1', 'variant2'],
            true
        );
        $this->assertEquals($variant, true);
        $this->assertEquals($feature->variant('testFeature2'), 'variant3');
        $this->assertEquals($feature->variant('testFeature3'), 'variant5');

        $this->assertEquals(
            $feature->variantBucketingBy('testFeature2', 'testid1'),
            'variant4'
        );
        $this->assertEquals(
            $feature->variantBucketingBy('testFeature3', 'testid2'),
            'variant6'
        );

        $feature->changeUser(['id' => 'anotheruser', 'uaid' => 'string3']);

        $this->assertEquals($feature->variant('testFeature2'), 'variant4');
        $this->assertEquals($feature->variant('testFeature3'), 'variant6');
    }
}
