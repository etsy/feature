<?php

declare(strict_types=1);

namespace PabloJoan\Feature\Value;

/**
 * A feature that can be enabled, disabled, ramped up, and A/B tested, as well
 * as enabled for certain classes of users.
 */
class Feature
{
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

    function __construct (Name $name, array $feature)
    {
        $enabled = $feature['enabled'] ?? 0;
        $description = $feature['description'] ?? '';
        $users = $feature['users'] ?? [];
        $groups = $feature['groups'] ?? [];
        $sources = $feature['sources'] ?? [];
        $admin = $feature['admin'] ?? '';
        $internal = $feature['internal'] ?? '';
        $publicUrlOverride = $feature['public_url_override'] ?? false;
        $excludeFrom = $feature['exclude_from'] ?? [];
        $start = $feature['start'] ?? '';
        $end = $feature['end'] ?? '';
        $bucketing = $feature['bucketing'] ?? 'random';

        $this->name = $name;
        $this->enabled = new Enabled($enabled);
        $this->description = new Description($description);
        $this->users = new Users($users);
        $this->groups = new Groups($groups);
        $this->sources = new Sources($sources);
        $this->admin = new Admin($admin);
        $this->internal = new Internal($internal);
        $this->publicUrlOverride = new PublicUrlOverride($publicUrlOverride);
        $this->excludeFrom = new ExcludeFrom($excludeFrom);
        $this->time = new Time($start, $end);
        $this->bucketing = new Bucketing($bucketing);
    }

    function name () : Name { return $this->name; }

    function enabled () : Enabled { return $this->enabled; }

    function description () : Description { return $this->description; }

    function users () : Users { return $this->users; }

    function groups () : Groups { return $this->groups; }

    function sources () : Sources { return $this->sources; }

    function admin () : Admin { return $this->admin; }

    function internal () : Internal { return $this->internal; }

    function publicUrlOverride () : PublicUrlOverride
    {
        return $this->publicUrlOverride;
    }

    function excludeFrom () : ExcludeFrom { return $this->excludeFrom; }

    function time () : Time { return $this->time; }

    function bucketing () : Bucketing { return $this->bucketing; }
}
