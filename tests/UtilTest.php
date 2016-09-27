<?php

namespace CafeMedia\Feature\Tests;

use CafeMedia\Feature\Util;

/**
 * Class UtilTest
 * @package CafeMedia\Feature\Tests
 */
class UtilTest extends \PHPUnit_Framework_TestCase
{
    public function testArrayGet()
    {
        $this->assertEquals(Util::arrayGet('test', 'test'), null);
        $this->assertEquals(Util::arrayGet(array('test' => 'test'), 'test'), 'test');
    }
}
