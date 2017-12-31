<?php

declare(strict_types=1);

namespace PabloJoan\Feature\Tests\Value;

use PabloJoan\Feature\Value\CalculateBucketingId;
use PabloJoan\Feature\Contract\{ Bucketing, User };
use PHPUnit\Framework\TestCase;

class CalculateBucketingIdTest extends TestCase
{
    function testId ()
    {
        $user = new class ([]) implements User {
            function __construct (array $user) { unset($user); }
            function uaid () : string { return ''; }
            function id () : string { return ''; }
            function country () : string { return ''; }
            function zipcode () : string { return ''; }
            function region () : string { return ''; }
            function isAdmin () : bool { return false; }
            function internalIP () : bool { return false; }
            function group () : string { return ''; }
        };
        $bucketing = new class ('') implements Bucketing {
            function __construct (string $bucketBy) { unset($bucketBy); }
            function __toString () : string { return 'random'; }
        };
        $bucketing = new CalculateBucketingId($user, $bucketing);
        $this->assertEquals($bucketing->id(), 'no uaid');

        $user = new class ([]) implements User {
            function __construct (array $user) { unset($user); }
            function uaid () : string { return 'test'; }
            function id () : string { return ''; }
            function country () : string { return ''; }
            function zipcode () : string { return ''; }
            function region () : string { return ''; }
            function isAdmin () : bool { return false; }
            function internalIP () : bool { return false; }
            function group () : string { return ''; }
        };
        $bucketing = new class ('') implements Bucketing {
            function __construct (string $bucketBy) { unset($bucketBy); }
            function __toString () : string { return 'random'; }
        };
        $bucketing = new CalculateBucketingId($user, $bucketing);
        $this->assertEquals($bucketing->id(), 'test');

        $user = new class ([]) implements User {
            function __construct (array $user) { unset($user); }
            function uaid () : string { return ''; }
            function id () : string { return 'test'; }
            function country () : string { return ''; }
            function zipcode () : string { return ''; }
            function region () : string { return ''; }
            function isAdmin () : bool { return false; }
            function internalIP () : bool { return false; }
            function group () : string { return ''; }
        };
        $bucketing = new class ('') implements Bucketing {
            function __construct (string $bucketBy) { unset($bucketBy); }
            function __toString () : string { return 'user'; }
        };
        $bucketing = new CalculateBucketingId($user, $bucketing);
        $this->assertEquals($bucketing->id(), 'test');

        $user = new class ([]) implements User {
            function __construct (array $user) { unset($user); }
            function uaid () : string { return 'test'; }
            function id () : string { return ''; }
            function country () : string { return ''; }
            function zipcode () : string { return ''; }
            function region () : string { return ''; }
            function isAdmin () : bool { return false; }
            function internalIP () : bool { return false; }
            function group () : string { return ''; }
        };
        $bucketing = new class ('') implements Bucketing {
            function __construct (string $bucketBy) { unset($bucketBy); }
            function __toString () : string { return 'uaid'; }
        };
        $bucketing = new CalculateBucketingId($user, $bucketing);
        $this->assertEquals($bucketing->id(), 'test');

        $user = new class ([]) implements User {
            function __construct (array $user) { unset($user); }
            function uaid () : string { return ''; }
            function id () : string { return ''; }
            function country () : string { return ''; }
            function zipcode () : string { return ''; }
            function region () : string { return ''; }
            function isAdmin () : bool { return false; }
            function internalIP () : bool { return false; }
            function group () : string { return ''; }
        };
        $bucketing = new class ('') implements Bucketing {
            function __construct (string $bucketBy) { unset($bucketBy); }
            function __toString () : string { return 'uaid'; }
        };
        try {
            (new CalculateBucketingId($user, $bucketing))->id();
        }
        catch (\Exception $e)
        {
            $this->assertEquals(
                $e->getMessage(),
                'user uaid must be provided if uaid bucketing is enabled.'
            );
        }

        $user = new class ([]) implements User {
            function __construct (array $user) { unset($user); }
            function uaid () : string { return ''; }
            function id () : string { return ''; }
            function country () : string { return ''; }
            function zipcode () : string { return ''; }
            function region () : string { return ''; }
            function isAdmin () : bool { return false; }
            function internalIP () : bool { return false; }
            function group () : string { return ''; }
        };
        $bucketing = new class ('') implements Bucketing {
            function __construct (string $bucketBy) { unset($bucketBy); }
            function __toString () : string { return 'user'; }
        };
        try {
            (new CalculateBucketingId($user, $bucketing))->id();
        }
        catch (\Exception $e)
        {
            $this->assertEquals(
                $e->getMessage(),
                'user id must be provided if user bucketing is enabled.'
            );
        }

        $user = new class ([]) implements User {
            function __construct (array $user) { unset($user); }
            function uaid () : string { return ''; }
            function id () : string { return ''; }
            function country () : string { return ''; }
            function zipcode () : string { return ''; }
            function region () : string { return ''; }
            function isAdmin () : bool { return false; }
            function internalIP () : bool { return false; }
            function group () : string { return ''; }
        };
        $bucketing = new class ('') implements Bucketing {
            function __construct (string $bucketBy) { unset($bucketBy); }
            function __toString () : string { return 'some other string'; }
        };
        try {
            (new CalculateBucketingId($user, $bucketing))->id();
        }
        catch (\Error $e)
        {
            $this->assertEquals(
                $e->getMessage(),
                'Return value of ' .
                'PabloJoan\Feature\Value\CalculateBucketingId::id() must ' .
                'implement interface PabloJoan\Feature\Contract\BucketingId, ' .
                'none returned'
            );
        }
    }
}
