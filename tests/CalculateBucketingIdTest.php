<?php

declare(strict_types=1);

namespace PabloJoan\Feature\Tests;

use PabloJoan\Feature\Value\{ CalculateBucketingId, Bucketing, User };
use PHPUnit\Framework\TestCase;

class CalculateBucketingIdTest extends TestCase
{
    function testId ()
    {
        $bucketing = new CalculateBucketingId(new User([]), new Bucketing('random'));
        $this->assertEquals($bucketing->id(), 'no uaid');

        $bucketing = new CalculateBucketingId(
            new User(['uaid' => 'test']),
            new Bucketing('random')
        );
        $this->assertEquals($bucketing->id(), 'test');

        $bucketing = new CalculateBucketingId(
            new User(['id' => 'test']),
            new Bucketing('user')
        );
        $this->assertEquals($bucketing->id(), 'test');

        $bucketing = new CalculateBucketingId(
            new User(['uaid' => 'test']),
            new Bucketing('uaid')
        );
        $this->assertEquals($bucketing->id(), 'test');

        try {
            (new CalculateBucketingId(new User([]), new Bucketing('uaid')))->id();
        }
        catch (\Exception $e)
        {
            $this->assertEquals(
                $e->getMessage(),
                'user uaid must be provided if uaid bucketing is enabled.'
            );
        }

        try {
            (new CalculateBucketingId(new User([]), new Bucketing('user')))->id();
        }
        catch (\Exception $e)
        {
            $this->assertEquals(
                $e->getMessage(),
                'user id must be provided if user bucketing is enabled.'
            );
        }
    }
}
