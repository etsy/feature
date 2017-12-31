<?php

declare(strict_types=1);

namespace PabloJoan\Feature\Tests\Value;

use PabloJoan\Feature\Value\Url;
use PabloJoan\Feature\Contract\Name;
use PHPUnit\Framework\TestCase;

class UrlTest extends TestCase
{
    function testVariant ()
    {
        $name = new class ('') implements Name {
            function __construct (string $name) { unset($name); }
            function __toString () : string { return 'test'; }
        };

        $url = new Url('');
        $this->assertEquals($url->variant($name), '');

        $url = new Url('http://www.testurl.com/');
        $this->assertEquals($url->variant($name), '');

        $url = new Url('http://www.testurl.com/?f=test:on');
        $this->assertEquals($url->variant($name), '');

        $url = new Url('http://www.testurl.com/?feature=test:on');
        $this->assertEquals($url->variant($name), 'on');

        $url = new Url('http://www.testurl.com/?feature=test:on,test:off');
        $this->assertEquals($url->variant($name), 'off');

        $url = new Url('http://www.testurl.com/?q=1&feature=test:off,test:on&a=2');
        $this->assertEquals($url->variant($name), 'on');

        try {
            new Url('bad url string');
        }
        catch (\Exception $e)
        {
            $this->assertEquals(
                $e->getMessage(),
                'bad url string is not a valid url.'
            );
        }
    }
}