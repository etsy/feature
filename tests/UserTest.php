<?php

namespace CafeMedia\Feature\Tests;

use CafeMedia\Feature\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    private $user;

    public function setup()
    {
        $this->user = new User([
            'user-uaid' => 'as54gerfd',
            'user-id' => 5,
            'user-name' => 'testUserName',
            'is-admin' => false,
            'user-group' => 'group',
            'internal-ip' => false,
            'zipcode' => 10203,
            'region' => 'ny',
            'country' => 'us'
        ]);
    }

    public function testUaid()
    {
        $this->assertEquals($this->user->uaid, 'as54gerfd');
    }

    public function testId()
    {
        $this->assertEquals($this->user->id, 5);
    }

    public function testName()
    {
        $this->assertEquals($this->user->name, 'testUserName');
    }

    public function testIsAdmin()
    {
        $this->assertEquals($this->user->isAdmin, false);
    }

    public function testGroup()
    {
        $this->assertEquals($this->user->group, 'group');
    }

    public function testInternalIP()
    {
        $this->assertEquals($this->user->internalIP, false);
    }

    public function testZipcode()
    {
        $this->assertEquals($this->user->zipcode, 10203);
    }

    public function testRegion()
    {
        $this->assertEquals($this->user->region, 'ny');
    }

    public function testCounrty()
    {
        $this->assertEquals($this->user->country, 'us');
    }
}