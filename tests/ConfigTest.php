<?php

declare(strict_types=1);

namespace PabloJoan\Feature\Tests;

use PabloJoan\Feature\Config;
use PabloJoan\Feature\Contract\{
    Feature,
    Name,
    Enabled,
    Description,
    Users,
    Groups,
    Sources,
    Admin,
    Internal,
    PublicUrlOverride,
    ExcludeFrom,
    Time,
    Bucketing,
    BucketingId,
    User,
    Url,
    Source
};
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    private $feature;
    private $config;

    function setUp ()
    {
        $user = new class ([]) implements User {
            function __construct (array $user) { unset($user); }
            function uaid () : string { return 'randomID'; }
            function id () : string { return ''; }
            function country () : string { return ''; }
            function zipcode () : string { return ''; }
            function region () : string { return ''; }
            function isAdmin () : bool { return false; }
            function internalIP () : bool { return false; }
            function group () : string { return ''; }
        };
        $url = new class ('') implements Url {
            function __construct (string $url) { unset($url); }
            function variant (Name $name) : string { return ''; }
        };
        $source = new class ('') implements Source {
            function __construct (string $source) { unset($source); }
            function variant () : string { return ''; }
        };
        $name = new class ('') implements Name {
            function __construct (string $name) { unset($name); }
            function __toString () : string { return 'test'; }
        };
        $admin = new class ('') implements Admin {
            function __construct (string $variant) { unset($variant); }
            function variant (User $user) : string { return 'test2'; }
        };
        $bucketing = new class ('') implements Bucketing {
            function __construct (string $bucketBy) { unset($bucketBy); }
            function __toString () : string { return 'uaid'; }
        };
        $description = new class ('') implements Description {
            function __construct (string $description) { unset($description); }
            function __toString () : string { return 'this is the description';}
        };
        $excludeFrom = new class ([]) implements ExcludeFrom {
            function __construct (array $excludeFrom) { unset($excludeFrom); }
            function variant (User $user) : string { return 'on'; }
        };
        $groups = new class ([]) implements Groups {
            function __construct (array $stanza) { unset($stanza); }
            function variant (User $user) : string { return 'test1'; }
        };
        $internal = new class ('') implements Internal {
            function __construct (string $variant) { unset($variant); }
            function variant (User $user) : string { return ''; }
        };
        $publicUrlOverride = new class (false) implements PublicUrlOverride {
            function __construct (bool $on) { unset($on); }
            function variant (Name $name, Url $url) : string { return 'test4'; }
        };
        $sources = new class ([]) implements Sources {
            function __construct (array $stanza) { unset($stanza); }
            function variant (Source $source) : string { return 'test3'; }
        };
        $users = new class ([]) implements Users {
            function __construct (array $stanza) { unset($stanza); }
            function variant (User $user) : string { return 'test4'; }
        };
        $time = new class ('', '') implements Time {
            function __construct (string $start, string $end) {
                unset($start);
                unset($end);
            }
            function variant () : string { return 'off'; }
        };
        $enabled = new class (0) implements Enabled {
            function __construct ($enabled) { unset($enabled); }
            function percentages () : array {
                return [
                    'test1' => 20,
                    'test2' => 50,
                    'test3' => 65,
                    'test4' => 100
                ];
            }
        };

        $features = [
            'admin' => $admin,
            'bucketing' => $bucketing,
            'description' => $description,
            'excludeFrom' => $excludeFrom,
            'groups' => $groups,
            'internal' => $internal,
            'publicUrlOverride' => $publicUrlOverride,
            'sources' => $sources,
            'users' => $users,
            'time' => $time,
            'enabled' => $enabled
        ];
        $feature = new class ($name, $features) implements Feature {
            private $name;
            private $enabled;
            private $description;
            private $users;
            private $groups;
            private $sources;
            private $admin;
            private $internal;
            private $publicUrlOverride;
            private $excludeFrom;
            private $time;
            private $bucketing;
            function __construct (Name $name, array $feature) {
                $this->name = $name;
                $this->admin = $feature['admin'];
                $this->bucketing = $feature['bucketing'];
                $this->description = $feature['description'];
                $this->excludeFrom = $feature['excludeFrom'];
                $this->groups = $feature['groups'];
                $this->internal = $feature['internal'];
                $this->publicUrlOverride = $feature['publicUrlOverride'];
                $this->sources = $feature['sources'];
                $this->users = $feature['users'];
                $this->time = $feature['time'];
                $this->enabled = $feature['enabled'];
            }
            function name () : Name { return $this->name; }
            function enabled () : Enabled { return $this->enabled; }
            function description () : Description { return $this->description; }
            function users () : Users { return $this->users; }
            function groups () : Groups { return $this->groups; }
            function sources () : Sources { return $this->sources; }
            function admin () : Admin { return $this->admin; }
            function internal () : Internal { return $this->internal; }
            function publicUrlOverride () : PublicUrlOverride {
                return $this->publicUrlOverride;
            }
            function excludeFrom () : ExcludeFrom { return $this->excludeFrom; }
            function time () : Time { return $this->time; }
            function bucketing () : Bucketing { return $this->bucketing; }
        };

        $this->config = new Config($user, $url, $source);
        $this->feature = $feature;
    }

    function testIsEnabled ()
    {
        $this->assertEquals($this->config->isEnabled($this->feature), true);
    }

    function testVariant ()
    {
        $this->assertEquals($this->config->variant($this->feature), 'test4');
    }

    function testIsEnabledBucketingBy ()
    {
        $bucketingId = new class ('') implements BucketingId {
            function __construct (string $id) { unset($id); }
            function __toString () : string { return 'test'; }
        };
        $this->assertEquals(
            $this->config->isEnabledBucketingBy($this->feature, $bucketingId),
            true
        );
    }

    function testVariantBucketingBy ()
    {
        $bucketingId = new class ('') implements BucketingId {
            function __construct (string $id) { unset($id); }
            function __toString () : string { return 'as54gerfd'; }
        };
        $this->assertEquals(
            $this->config->variantBucketingBy($this->feature, $bucketingId),
            'test4'
        );
    }
}
