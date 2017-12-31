<?php

declare(strict_types=1);

namespace PabloJoan\Feature\Tests\Value;

use PabloJoan\Feature\Value\Bucketing;
use PHPUnit\Framework\TestCase;

class BucketingTest extends TestCase
{
    function testBucketing ()
    {
        $bucketing = new Bucketing('random');
        $this->assertEquals((string) $bucketing, 'random');

        $bucketing = new Bucketing('uaid');
        $this->assertEquals((string) $bucketing, 'uaid');

        $bucketing = new Bucketing('user');
        $this->assertEquals((string) $bucketing, 'user');

        try {
            new Bucketing('some other string');
        }
        catch (\Exception $e)
        {
            $this->assertEquals(
                $e->getMessage(),
                'bucketing must be either "random", "uaid" or "user". some other string'
            );
        }
    }
}
