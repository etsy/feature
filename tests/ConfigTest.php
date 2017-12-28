<?php

declare(strict_types=1);

namespace PabloJoan\Feature\Tests;

use PabloJoan\Feature\Config;
use PabloJoan\Feature\Value\{ User, BucketingId, Url, Source, Feature, Name };
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    private $feature;
    private $config;

    function setUp ()
    {
        $this->config = new Config(new User([]), new Url(''), new Source(''));
        $this->feature = new Feature(
            new Name('test'),
            [
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
            ]
        );
    }

    function testIsEnabled ()
    {
        $this->assertEquals($this->config->isEnabled($this->feature), true);
    }

    function testVariant ()
    {
        $variant = in_array(
            $this->config->variant($this->feature),
            ['test1', 'test2', 'test3', 'test4'],
            true
        );
        $this->assertEquals($variant, true);
    }

    function testIsEnabledBucketingBy ()
    {
        $this->assertEquals(
            $this->config->isEnabledBucketingBy(
                $this->feature,
                new BucketingId('test')
            ),
            true
        );
    }

    function testVariantBucketingBy ()
    {
        $variant = in_array(
            $this->config->variantBucketingBy(
                $this->feature,
                new BucketingId('as54gerfd')
            ),
            ['test1', 'test2', 'test3', 'test4'],
            true
        );
        $this->assertEquals($variant, true);
    }
}
