<?php

declare(strict_types=1);

namespace PabloJoan\Feature\Tests;

use PabloJoan\Feature\Value\{ ExcludeFrom, User };
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
        $this->assertEquals($excludeFrom->variant(new User(['zipcode' => '10014'])), 'off');
        $this->assertEquals($excludeFrom->variant(new User(['zipcode' => '10015'])), '');
        $this->assertEquals($excludeFrom->variant(new User(['country' => 'us'])), 'off');
        $this->assertEquals($excludeFrom->variant(new User(['country' => 'ur'])), '');
        $this->assertEquals($excludeFrom->variant(new User(['region' => 'ny'])), 'off');
        $this->assertEquals($excludeFrom->variant(new User(['region' => 'nn'])), '');

        $excludeFrom = new ExcludeFrom([]);
        $this->assertEquals($excludeFrom->variant(new User([])), '');

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
