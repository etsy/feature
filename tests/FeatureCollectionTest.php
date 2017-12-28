<?php

declare(strict_types=1);

namespace PabloJoan\Feature\Tests;

use PabloJoan\Feature\Value\{ FeatureCollection, Feature, Name };
use PHPUnit\Framework\TestCase;

class FeatureCollectionTest extends TestCase
{
    function testCollection ()
    {
        $features = new FeatureCollection(['test' => ['enabled' => 0]]);
        $this->assertEquals(
            $features->get(new Name('test')),
            new Feature(new Name('test'), ['enabled' => 0])
        );

        $features->change(new Name('test'), ['enabled' => 100]);
        $this->assertEquals(
            $features->get(new Name('test')),
            new Feature(new Name('test'), ['enabled' => 100])
        );

        try {
            $features->change(new Name('i dont exist'), []);
        }
        catch (\Exception $e)
        {
            $this->assertEquals(
                $e->getMessage(),
                "feature 'i dont exist' does not exist."
            );
        }
    }
}
