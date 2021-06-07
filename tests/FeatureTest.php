<?php

declare(strict_types=1);

namespace PabloJoan\Feature\Tests;

use PabloJoan\Feature\Feature;
use PHPUnit\Framework\TestCase;

class FeatureTest extends TestCase
{
    public function testFeatures(): void
    {
        $feature = new Feature([
            'test_feature_1' => [
                'enabled' => 100
            ],
            'test_feature_2' => [
                'enabled' => 0
            ],
            'test_feature_3' => [
                'enabled' => 50,
                'bucketing' => 'id'
            ],
            'test_feature_4' => [
                'enabled' => [
                    'test1' => 20,
                    'test2' => 30,
                    'test3' => 15,
                    'test4' => 35
                ],
                'bucketing' => 'id'
            ],
            'test_feature_5' => [
                'enabled' => 0,
                'bucketing' => 'random'
            ],
            'test_feature_6' => [
                'enabled' => 0,
                'bucketing' => 'id'
            ],
            'test_feature_7' => [
                'enabled' => 100,
                'bucketing' => 'random'
            ],
            'test_feature_8' => [
                'enabled' => 100,
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

        $this->assertEquals($feature->isEnabled('test_feature_1', 'test'), true);
        $this->assertEquals($feature->isEnabled('test_feature_2', 'test'), false);
        $this->assertEquals($feature->isEnabled('test_feature_3', 'test'), false);
        $this->assertEquals($feature->isEnabled('test_feature_4', 'test'), true);
        $this->assertEquals($feature->isEnabled('test_feature_5', 'test'), false);
        $this->assertEquals($feature->isEnabled('test_feature_6', 'test'), false);
        $this->assertEquals($feature->isEnabled('test_feature_7', 'test'), true);
        $this->assertEquals($feature->isEnabled('test_feature_8', 'test'), true);

        $this->assertEquals($feature->variant('test_feature_1'), 'test_feature_1');
        $this->assertEquals($feature->variant('test_feature_2'), '');
        $this->assertEquals($feature->variant('test_feature_3'), 'test_feature_3');
        $this->assertEquals($feature->variant('test_feature_4'), 'test2');
        $this->assertEquals($feature->variant('test_feature_5'), '');
        $this->assertEquals($feature->variant('test_feature_6'), '');
        $this->assertEquals($feature->variant('test_feature_7'), 'test_feature_7');
        $this->assertEquals($feature->variant('test_feature_8'), 'test_feature_8');

        $this->assertEquals($feature->variant('test_feature_1', 'test'), 'test_feature_1');
        $this->assertEquals($feature->variant('test_feature_2', 'test'), '');
        $this->assertEquals($feature->variant('test_feature_3', 'test'), '');
        $this->assertEquals($feature->variant('test_feature_4', 'test'), 'test4');
        $this->assertEquals($feature->variant('test_feature_5', 'test'), '');
        $this->assertEquals($feature->variant('test_feature_6', 'test'), '');
        $this->assertEquals($feature->variant('test_feature_7', 'test'), 'test_feature_7');
        $this->assertEquals($feature->variant('test_feature_8', 'test'), 'test_feature_8');

        try {
            $feature = new Feature([
                'test_feature_3' => [
                    'enabled' => 50,
                    'bucketing' => 'not supported bucketing'
                ]
            ]);
        } catch (\Exception $e) {
            $this->assertEquals($e->getMessage(), 'bucketing option: not supported bucketing not supported.');
        }
    }
}
