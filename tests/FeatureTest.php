<?php

namespace CafeMedia\Feature\Tests;

use CafeMedia\Feature\Feature;
use CafeMedia\Feature\Instance;
use PHPUnit_Framework_TestCase;

/**
 * Class FeatureTest
 * @package CafeMedia\Feature\Tests
 */
class FeatureTest extends PHPUnit_Framework_TestCase
{
    private $feature;

    public function setUp()
    {
        $this->feature = new Feature($this->getMock('Psr\Log\LoggerInterface'));
    }

    public function testGetInstance()
    {
        $this->assertEquals($this->feature->getInstance() instanceof Instance, true);
    }

    public function testIsEnabled()
    {
        $this->assertEquals($this->feature->isEnabled('test'), false);
        $this->assertEquals($this->feature->getInstance()->isEnabled('test'), false);
    }

    public function testIsEnabledFor()
    {
        $this->assertEquals($this->feature->isEnabledFor('test', (object) array('user_id' => 1)), false);
        $this->assertEquals($this->feature->getInstance()->isEnabledFor('test', (object) array('user_id' => 1)), false);
    }

    public function testIsEnabledBucketingBy()
    {
        $this->assertEquals($this->feature->isEnabledBucketingBy('test', 'test'), false);
        $this->assertEquals($this->feature->getInstance()->isEnabledBucketingBy('test', 'test'), false);
    }

    public function testVariant()
    {
        $this->assertEquals($this->feature->variant('test'), 'off');
        $this->assertEquals($this->feature->getInstance()->variant('test'), 'off');
    }

    public function testVariantFor()
    {
        $this->assertEquals($this->feature->variantFor('test', (object) array('user_id' => 1)), 'off');
        $this->assertEquals($this->feature->getInstance()->variantFor('test', (object) array('user_id' => 1)), 'off');
    }

    public function testVariantBucketingBy()
    {
        $this->assertEquals($this->feature->variantBucketingBy('test', 'test'), 'off');
        $this->assertEquals($this->feature->getInstance()->variantBucketingBy('test', 'test'), 'off');
    }

    public function testDescription()
    {
        $this->assertEquals($this->feature->description('test'), 'No description.');
    }

    public function testData()
    {
        $this->assertEquals($this->feature->data('test'), array());
    }

    public function testVariantData()
    {
        $this->assertEquals($this->feature->variantData('test'), array());
    }

    public function testGetGACustomVarJS()
    {
        $this->assertEquals(
            $this->feature->getInstance()->getGACustomVarJS('test'),
            "_gaq.push(['_setCustomVar', 3, 'AB', 'null', 3]);"
        );

        $this->assertEquals(
            $this->feature->getInstance()->getGACustomVarJS('mobile'),
            "['_setCustomVar', 3, 'AB', 'null', 3],"
        );
    }
}
