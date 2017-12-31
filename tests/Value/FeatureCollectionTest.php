<?php

declare(strict_types=1);

namespace PabloJoan\Feature\Tests\Value;

use PabloJoan\Feature\Value\FeatureCollection;
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
    User,
    Url,
    Source
};
use PHPUnit\Framework\TestCase;

class FeatureCollectionTest extends TestCase
{
    function testCollection ()
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
            function variant (User $user) : string { return ''; }
        };
        $bucketing = new class ('') implements Bucketing {
            function __construct (string $bucketBy) { unset($bucketBy); }
            function __toString () : string { return 'random'; }
        };
        $description = new class ('') implements Description {
            function __construct (string $description) { unset($description); }
            function __toString () : string { return ''; }
        };
        $excludeFrom = new class ([]) implements ExcludeFrom {
            function __construct (array $excludeFrom) { unset($excludeFrom); }
            function variant (User $user) : string { return ''; }
        };
        $groups = new class ([]) implements Groups {
            function __construct (array $stanza) { unset($stanza); }
            function variant (User $user) : string { return ''; }
        };
        $internal = new class ('') implements Internal {
            function __construct (string $variant) { unset($variant); }
            function variant (User $user) : string { return ''; }
        };
        $publicUrlOverride = new class (false) implements PublicUrlOverride {
            function __construct (bool $on) { unset($on); }
            function variant (Name $name, Url $url) : string { return ''; }
        };
        $sources = new class ([]) implements Sources {
            function __construct (array $stanza) { unset($stanza); }
            function variant (Source $source) : string { return ''; }
        };
        $users = new class ([]) implements Users {
            function __construct (array $stanza) { unset($stanza); }
            function variant (User $user) : string { return ''; }
        };
        $time = new class ('', '') implements Time {
            function __construct (string $start, string $end) {
                unset($start);
                unset($end);
            }
            function variant () : string { return ''; }
        };
        $enabled = new class (0) implements Enabled {
            function __construct ($enabled) { unset($enabled); }
            function percentages () : array { return ['on' => 0]; }
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
        $collection = new FeatureCollection(['test' => ['enabled' => 0]]);
        $this->assertEquals(
            (string) $collection->get($name)->name(),
            (string) $feature->name()
        );
        $this->assertEquals(
            $collection->get($name)->publicUrlOverride()->variant($name, $url),
            $feature->publicUrlOverride()->variant($name, $url)
        );
        $this->assertEquals(
            $collection->get($name)->users()->variant($user),
            $feature->users()->variant($user)
        );
        $this->assertEquals(
            $collection->get($name)->sources()->variant($source),
            $feature->sources()->variant($source)
        );
        $this->assertEquals(
            $collection->get($name)->groups()->variant($user),
            $feature->groups()->variant($user)
        );
        $this->assertEquals(
            $collection->get($name)->admin()->variant($user),
            $feature->admin()->variant($user)
        );
        $this->assertEquals(
            $collection->get($name)->internal()->variant($user),
            $feature->internal()->variant($user)
        );
        $this->assertEquals(
            $collection->get($name)->excludeFrom()->variant($user),
            $feature->excludeFrom()->variant($user)
        );
        $this->assertEquals(
            $collection->get($name)->time()->variant(),
            $feature->time()->variant()
        );
        $this->assertEquals(
            $collection->get($name)->enabled()->percentages(),
            $feature->enabled()->percentages()
        );
        $this->assertEquals(
            (string) $collection->get($name)->bucketing(),
            (string) $feature->bucketing()
        );

        $collection->change($name, ['enabled' => 100]);
        $enabled = new class (0) implements Enabled {
            function __construct ($enabled) { unset($enabled); }
            function percentages () : array { return ['on' => 100]; }
        };
        $features['enabled'] = $enabled;
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
        $this->assertEquals(
            (string) $collection->get($name)->name(),
            (string) $feature->name()
        );
        $this->assertEquals(
            $collection->get($name)->publicUrlOverride()->variant($name, $url),
            $feature->publicUrlOverride()->variant($name, $url)
        );
        $this->assertEquals(
            $collection->get($name)->users()->variant($user),
            $feature->users()->variant($user)
        );
        $this->assertEquals(
            $collection->get($name)->sources()->variant($source),
            $feature->sources()->variant($source)
        );
        $this->assertEquals(
            $collection->get($name)->groups()->variant($user),
            $feature->groups()->variant($user)
        );
        $this->assertEquals(
            $collection->get($name)->admin()->variant($user),
            $feature->admin()->variant($user)
        );
        $this->assertEquals(
            $collection->get($name)->internal()->variant($user),
            $feature->internal()->variant($user)
        );
        $this->assertEquals(
            $collection->get($name)->excludeFrom()->variant($user),
            $feature->excludeFrom()->variant($user)
        );
        $this->assertEquals(
            $collection->get($name)->time()->variant(),
            $feature->time()->variant()
        );
        $this->assertEquals(
            $collection->get($name)->enabled()->percentages(),
            $feature->enabled()->percentages()
        );
        $this->assertEquals(
            (string) $collection->get($name)->bucketing(),
            (string) $feature->bucketing()
        );

        $name = new class ('') implements Name {
            function __construct (string $name) { unset($name); }
            function __toString () : string { return 'i dont exist'; }
        };
        try {
            $collection->change($name, []);
        }
        catch (\Exception $e)
        {
            $this->assertEquals(
                $e->getMessage(),
                "feature 'i dont exist' does not exist."
            );
        }

        $name = new class ('') implements Name {
            function __construct (string $name) { unset($name); }
            function __toString () : string { return 'test'; }
        };
        try {
            $collection->add($name, []);
        }
        catch (\Exception $e)
        {
            $this->assertEquals($e->getMessage(),
                "feature 'test' already exists."
            );
        }

        $name = new class ('') implements Name {
            function __construct (string $name) { unset($name); }
            function __toString () : string { return 'newFeature'; }
        };
        $collection->add($name, ['enabled' => 100]);
        $this->assertEquals(
            $collection->get($name)->enabled()->percentages(),
            ['on' => 100]
        );

        $collection->remove($name);
        $this->assertEquals(
            $collection->get($name)->enabled()->percentages(),
            ['on' => 0]
        );
    }
}
