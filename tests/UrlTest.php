<?php

declare(strict_types=1);

namespace PabloJoan\Feature\Tests;

use PabloJoan\Feature\Value\{ Url, Name };
use PHPUnit\Framework\TestCase;

class UrlTest extends TestCase
{
    function testVariant ()
    {
        $url = new Url('');
        $this->assertEquals($url->variant(new Name('test')), '');

        $url = new Url('http://www.testurl.com/');
        $this->assertEquals($url->variant(new Name('test')), '');

        $url = new Url('http://www.testurl.com/?f=test:on');
        $this->assertEquals($url->variant(new Name('test')), '');

        $url = new Url('http://www.testurl.com/?feature=test:on');
        $this->assertEquals($url->variant(new Name('test')), 'on');

        $url = new Url('http://www.testurl.com/?feature=test:on,test:off');
        $this->assertEquals($url->variant(new Name('test')), 'on');

        $url = new Url('http://www.testurl.com/?q=1&feature=test:off,test:on&a=2');
        $this->assertEquals($url->variant(new Name('test')), 'off');

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