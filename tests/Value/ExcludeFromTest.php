<?php

declare(strict_types=1);

namespace PabloJoan\Feature\Tests\Value;

use PabloJoan\Feature\Value\ExcludeFrom;
use PabloJoan\Feature\Contract\User;
use PHPUnit\Framework\TestCase;

class ExcludeFromTest extends TestCase
{
    function testVariant ()
    {
        $excludeFrom = new ExcludeFrom([
            'zips' => ['10014', '10023'],
            'countries' => ['us', 'rd'],
            'regions' => ['ny', 'nj', 'ca']
        ]);

        $user = new class ([]) implements User {
            function __construct (array $user) { unset($user); }
            function uaid () : string { return ''; }
            function id () : string { return ''; }
            function country () : string { return ''; }
            function zipcode () : string { return '10014'; }
            function region () : string { return ''; }
            function isAdmin () : bool { return false; }
            function internalIP () : bool { return false; }
            function group () : string { return ''; }
        };
        $this->assertEquals($excludeFrom->variant($user), 'off');

        $user = new class ([]) implements User {
            function __construct (array $user) { unset($user); }
            function uaid () : string { return ''; }
            function id () : string { return ''; }
            function country () : string { return ''; }
            function zipcode () : string { return '10015'; }
            function region () : string { return ''; }
            function isAdmin () : bool { return false; }
            function internalIP () : bool { return false; }
            function group () : string { return ''; }
        };
        $this->assertEquals($excludeFrom->variant($user), '');

        $user = new class ([]) implements User {
            function __construct (array $user) { unset($user); }
            function uaid () : string { return ''; }
            function id () : string { return ''; }
            function country () : string { return 'us'; }
            function zipcode () : string { return ''; }
            function region () : string { return ''; }
            function isAdmin () : bool { return false; }
            function internalIP () : bool { return false; }
            function group () : string { return ''; }
        };
        $this->assertEquals($excludeFrom->variant($user), 'off');

        $user = new class ([]) implements User {
            function __construct (array $user) { unset($user); }
            function uaid () : string { return ''; }
            function id () : string { return ''; }
            function country () : string { return 'ur'; }
            function zipcode () : string { return ''; }
            function region () : string { return ''; }
            function isAdmin () : bool { return false; }
            function internalIP () : bool { return false; }
            function group () : string { return ''; }
        };
        $this->assertEquals($excludeFrom->variant($user), '');

        $user = new class ([]) implements User {
            function __construct (array $user) { unset($user); }
            function uaid () : string { return ''; }
            function id () : string { return ''; }
            function country () : string { return ''; }
            function zipcode () : string { return ''; }
            function region () : string { return 'ny'; }
            function isAdmin () : bool { return false; }
            function internalIP () : bool { return false; }
            function group () : string { return ''; }
        };
        $this->assertEquals($excludeFrom->variant($user), 'off');

        $user = new class ([]) implements User {
            function __construct (array $user) { unset($user); }
            function uaid () : string { return ''; }
            function id () : string { return ''; }
            function country () : string { return ''; }
            function zipcode () : string { return ''; }
            function region () : string { return 'nn'; }
            function isAdmin () : bool { return false; }
            function internalIP () : bool { return false; }
            function group () : string { return ''; }
        };
        $this->assertEquals($excludeFrom->variant($user), '');

        $excludeFrom = new ExcludeFrom([]);
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
        $this->assertEquals($excludeFrom->variant($user), '');

        try {
            new ExcludeFrom(['bad array' => 'with other stuff']);
        }
        catch (\Exception $e)
        {
            $this->assertEquals(
                $e->getMessage(),
                'bad exclude_from stanza {"bad array":"with other stuff"}'
            );
        }
    }
}
