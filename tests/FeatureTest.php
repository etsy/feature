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
            '1' => [
                'enabled' => 100
            ],
            '2' => [
                'enabled' => 0
            ],
            '3' => [
                'enabled' => 50,
                'bucketing' => 'id'
            ],
            '4' => [
                'enabled' => [
                    'test1' => 20,
                    'test2' => 30,
                    'test3' => 15,
                    'test4' => 35
                ],
                'bucketing' => 'id'
            ],
            '5' => [
                'enabled' => 0,
                'bucketing' => 'random'
            ],
            '6' => [
                'enabled' => 0,
                'bucketing' => 'id'
            ],
            '7' => [
                'enabled' => 100,
                'bucketing' => 'random'
            ],
            '8' => [
                'enabled' => 100,
                'bucketing' => 'id'
            ]
        ]);

        $this->assertEquals($feature->isEnabled('1'), true);
        $this->assertEquals($feature->isEnabled('2'), false);
        $this->assertEquals($feature->isEnabled('3'), false);
        $this->assertEquals($feature->isEnabled('4'), true);
        $this->assertEquals($feature->isEnabled('5'), false);
        $this->assertEquals($feature->isEnabled('6'), false);
        $this->assertEquals($feature->isEnabled('7'), true);
        $this->assertEquals($feature->isEnabled('8'), true);

        $this->assertEquals($feature->isEnabled('1', 'test'), true);
        $this->assertEquals($feature->isEnabled('2', 'test'), false);
        $this->assertEquals($feature->isEnabled('3', 'test'), false);
        $this->assertEquals($feature->isEnabled('4', 'test'), true);
        $this->assertEquals($feature->isEnabled('5', 'test'), false);
        $this->assertEquals($feature->isEnabled('6', 'test'), false);
        $this->assertEquals($feature->isEnabled('7', 'test'), true);
        $this->assertEquals($feature->isEnabled('8', 'test'), true);

        $this->assertEquals($feature->variant('1'), '0');
        $this->assertEquals($feature->variant('2'), '');
        $this->assertEquals($feature->variant('3'), '');
        $this->assertEquals($feature->variant('4'), 'test4');
        $this->assertEquals($feature->variant('5'), '');
        $this->assertEquals($feature->variant('6'), '');
        $this->assertEquals($feature->variant('7'), '0');
        $this->assertEquals($feature->variant('8'), '0');

        $this->assertEquals($feature->variant('1', 'test'), '0');
        $this->assertEquals($feature->variant('2', 'test'), '');
        $this->assertEquals($feature->variant('3', 'test'), '');
        $this->assertEquals($feature->variant('4', 'test'), 'test4');
        $this->assertEquals($feature->variant('5', 'test'), '');
        $this->assertEquals($feature->variant('6', 'test'), '');
        $this->assertEquals($feature->variant('7', 'test'), '0');
        $this->assertEquals($feature->variant('8', 'test'), '0');

        try {
            $feature = new Feature([
                '3' => [
                    'enabled' => 50,
                    'bucketing' => 'not supported bucketing'
                ]
            ]);
        } catch (\Exception $e) {
            $this->assertEquals($e->getMessage(), 'bucketing option: not supported bucketing not supported.');
        }
    }
}
