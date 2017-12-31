<?php

declare(strict_types=1);

namespace PabloJoan\Feature\Tests\Value;

use PabloJoan\Feature\Value\Enabled;
use PHPUnit\Framework\TestCase;

class EnabledTest extends TestCase
{
    function testPercentages ()
    {
        $enabled = new Enabled(0);
        $this->assertEquals($enabled->percentages(), ['on' => 0]);

        $enabled = new Enabled(100);
        $this->assertEquals($enabled->percentages(), ['on' => 100]);

        $enabled = new Enabled(['on' => 50]);
        $this->assertEquals($enabled->percentages(), ['on' => 50]);

        $enabled = new Enabled(['test1' => 23, 'test2' => 48]);
        $this->assertEquals($enabled->percentages(), ['test1' => 23, 'test2' => 71]);

        $enabled = new Enabled(['test1' => 60, 'test2' => 40]);
        $this->assertEquals($enabled->percentages(), ['test1' => 60, 'test2' => 100]);

        try {
            new Enabled('string');
        }
        catch (\Exception $e)
        {
            $this->assertEquals(
                $e->getMessage(),
                'Malformed enabled property "string"'
            );
        }

        try {
            new Enabled(101);
        }
        catch (\Exception $e)
        {
            $this->assertEquals(
                $e->getMessage(),
                'Bad percentage 101'
            );
        }

        try {
            new Enabled(-1);
        }
        catch (\Exception $e)
        {
            $this->assertEquals(
                $e->getMessage(),
                'Bad percentage -1'
            );
        }

        try {
            new Enabled(['test1' => 60, 'test2' => 100]);
        }
        catch (\Exception $e)
        {
            $this->assertEquals(
                $e->getMessage(),
                'Total of percentages > 100: 160'
            );
        }
    }
}
