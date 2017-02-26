<?php

namespace CafeMedia\Feature\Tests;

use CafeMedia\Feature\Stanza;
use PHPUnit\Framework\TestCase;

class StanzaTest extends TestCase
{
    private $stanza;

    public function setUp()
    {
        $this->stanza = new Stanza([
            'description' => 'this is the description of the stanza',
            'enabled' => [
                'test1' => 20,
                'test2' => 30,
                'test3' => 15,
                'test4' => 35
            ],
            'users' => ['user1', 'user2', 'user3'],
            'groups' => ['group1', 'group2', 'group3'],
            'sources' => ['source1', 'source2', 'source3'],
            'admin' => 'test3',
            'internal' => 'test1',
            'public_url_override' => true,
            'bucketing' => 'random',
            'exclude_from' => [
                'zips' => [10014, 10023],
                'countries' => ['us', 'rd'],
                'regions' => ['ny', 'nj', 'ca']
            ],
            'start' => 20170314,
            'end' => 20170530
        ]);
    }

    public function testDescription()
    {
        $this->assertEquals(
            $this->stanza->description,
            'this is the description of the stanza'
        );
    }

    public function testEnabled()
    {
        $this->assertEquals(
            $this->stanza->enabled,
            [
                'on' => [
                    'test1' => 20,
                    'test2' => 30,
                    'test3' => 15,
                    'test4' => 35
                ]
            ]
        );
    }

    public function testUsers()
    {
        $this->assertEquals(
            $this->stanza->users,
            ['user1' => 'on', 'user2' => 'on', 'user3' => 'on']
        );
    }

    public function testGroups()
    {
        $this->assertEquals(
            $this->stanza->groups,
            ['group1' => 'on', 'group2' => 'on', 'group3' => 'on']
        );
    }

    public function testSources()
    {
        $this->assertEquals(
            $this->stanza->sources,
            ['source1' => 'on', 'source2' => 'on', 'source3' => 'on']
        );
    }

    public function testAdminVariant()
    {
        $this->assertEquals($this->stanza->adminVariant, 'test3');
    }

    public function testInternalVariant()
    {
        $this->assertEquals($this->stanza->internalVariant, 'test1');
    }

    public function testPublicUrlOverride()
    {
        $this->assertEquals($this->stanza->publicUrlOverride, true);
    }

    public function testBucketing()
    {
        $this->assertEquals($this->stanza->bucketing, 'random');
    }

    public function testExcludeFrom()
    {
        $this->assertEquals(
            $this->stanza->exludeFrom,
            [
                'zips' => [10014, 10023],
                'countries' => ['us', 'rd'],
                'regions' => ['ny', 'nj', 'ca']
            ]
        );
    }

    public function testStart()
    {
        $this->assertEquals($this->stanza->start, 1489449600);
    }

    public function testEnd()
    {
        $this->assertEquals($this->stanza->end, 1496102400);
    }
}