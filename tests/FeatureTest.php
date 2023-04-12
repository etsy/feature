<?php

declare(strict_types=1);

namespace PabloJoan\Feature\Tests;

use PabloJoan\Feature\Features;
use PHPUnit\Framework\TestCase;

class FeatureTest extends TestCase
{
    public function testFeatures(): void
    {
        $feature = new Features([
            'test_feature_1' => [
                'variants' => ['enabled' => 100]
            ],
            'test_feature_2' => [
                'variants' => ['enabled' => 0]
            ],
            'test_feature_3' => [
                'variants' => ['enabled' => 50],
                'bucketing' => 'id'
            ],
            'test_feature_4' => [
                'variants' => [
                    'test1' => 20,
                    'test2' => 30,
                    'test3' => 15,
                    'test4' => 35
                ],
                'bucketing' => 'id'
            ],
            'test_feature_5' => [
                'variants' => ['enabled' => 0],
                'bucketing' => 'random'
            ],
            'test_feature_6' => [
                'variants' => ['enabled' => 0],
                'bucketing' => 'id'
            ],
            'test_feature_7' => [
                'variants' => ['enabled' => 100],
                'bucketing' => 'random'
            ],
            'test_feature_8' => [
                'variants' => ['enabled' => 100],
                'bucketing' => 'id'
            ]
        ]);

        $this->assertEquals($feature->isEnabled('test_feature_1'), true);
        $this->assertEquals($feature->isEnabled('test_feature_2'), false);
        $this->assertEquals($feature->isEnabled('test_feature_3'), true);
        $this->assertEquals($feature->isEnabled('test_feature_4'), true);
        $this->assertEquals($feature->isEnabled('test_feature_5'), false);
        $this->assertEquals($feature->isEnabled('test_feature_6'), false);
        $this->assertEquals($feature->isEnabled('test_feature_7'), true);
        $this->assertEquals($feature->isEnabled('test_feature_8'), true);

        $this->assertEquals(
            $feature->isEnabled('test_feature_1', 'test'),
            true
        );
        $this->assertEquals(
            $feature->isEnabled('test_feature_2', 'test'),
            false
        );
        $this->assertEquals(
            $feature->isEnabled('test_feature_3', 'test'),
            false
        );
        $this->assertEquals(
            $feature->isEnabled('test_feature_4', 'test'),
            true
        );
        $this->assertEquals(
            $feature->isEnabled('test_feature_5', 'test'),
            false
        );
        $this->assertEquals(
            $feature->isEnabled('test_feature_6', 'test'),
            false
        );
        $this->assertEquals(
            $feature->isEnabled('test_feature_7', 'test'),
            true
        );
        $this->assertEquals(
            $feature->isEnabled('test_feature_8', 'test'),
            true
        );

        $this->assertEquals(
            $feature->getEnabledVariant('test_feature_1'),
            'enabled'
        );
        $this->assertEquals(
            $feature->getEnabledVariant('test_feature_2'),
            ''
        );
        $this->assertEquals(
            $feature->getEnabledVariant('test_feature_3'),
            'enabled'
        );
        $this->assertEquals(
            $feature->getEnabledVariant('test_feature_4'),
            'test1'
        );
        $this->assertEquals(
            $feature->getEnabledVariant('test_feature_5'),
            ''
        );
        $this->assertEquals(
            $feature->getEnabledVariant('test_feature_6'),
            ''
        );
        $this->assertEquals(
            $feature->getEnabledVariant('test_feature_7'),
            'enabled'
        );
        $this->assertEquals(
            $feature->getEnabledVariant('test_feature_8'),
            'enabled'
        );

        $this->assertEquals(
            $feature->getEnabledVariant('test_feature_1', 'test'),
            'enabled'
        );
        $this->assertEquals(
            $feature->getEnabledVariant('test_feature_2', 'test'),
            ''
        );
        $this->assertEquals(
            $feature->getEnabledVariant('test_feature_3', 'test'),
            ''
        );
        $this->assertEquals(
            $feature->getEnabledVariant('test_feature_4', 'test'),
            'test3'
        );
        $this->assertEquals(
            $feature->getEnabledVariant('test_feature_5', 'test'),
            ''
        );
        $this->assertEquals(
            $feature->getEnabledVariant('test_feature_6', 'test'),
            ''
        );
        $this->assertEquals(
            $feature->getEnabledVariant('test_feature_7', 'test'),
            'enabled'
        );
        $this->assertEquals(
            $feature->getEnabledVariant('test_feature_8', 'test'),
            'enabled'
        );
    }
}
