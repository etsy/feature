<?php

declare(strict_types=1);

namespace PabloJoan\Feature\Value;

use PabloJoan\Feature\Contract\{
    Feature as FeatureContract,
    Name as NameContract,
    Enabled as EnabledContract,
    Description as DescriptionContract,
    Users as UsersContract,
    Groups as GroupsContract,
    Sources as SourcesContract,
    Admin as AdminContract,
    Internal as InternalContract,
    PublicUrlOverride as PublicUrlOverrideContract,
    ExcludeFrom as ExcludeFromContract,
    Time as TimeContract,
    Bucketing as BucketingContract
};

/**
 * A feature that can be enabled, disabled, ramped up, and A/B tested, as well
 * as enabled for certain classes of users.
 */
class Feature implements FeatureContract
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

    function __construct (NameContract $name, array $feature)
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

    function name () : NameContract { return $this->name; }

    function enabled () : EnabledContract { return $this->enabled; }

    function description () : DescriptionContract
    {
        return $this->description;
    }

    function users () : UsersContract { return $this->users; }

    function groups () : GroupsContract { return $this->groups; }

    function sources () : SourcesContract { return $this->sources; }

    function admin () : AdminContract { return $this->admin; }

    function internal () : InternalContract { return $this->internal; }

    function publicUrlOverride () : PublicUrlOverrideContract
    {
        return $this->publicUrlOverride;
    }

    function excludeFrom () : ExcludeFromContract { return $this->excludeFrom; }

    function time () : TimeContract { return $this->time; }

    function bucketing () : BucketingContract { return $this->bucketing; }
}
