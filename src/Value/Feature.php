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
    private $urlOverride;
    private $excludeFrom;
    private $time;
    private $bucketing;

    function __construct (string $name, array $feature)
    {
        $enabled     = $feature['enabled']      ?? 0;
        $users       = $feature['users']        ?? [];
        $groups      = $feature['groups']       ?? [];
        $sources     = $feature['sources']      ?? [];
        $excludeFrom = $feature['exclude_from'] ?? [];
        $description = $feature['description']  ?? '';
        $admin       = $feature['admin']        ?? '';
        $internal    = $feature['internal']     ?? '';
        $start       = $feature['start']        ?? '';
        $end         = $feature['end']          ?? '';
        $bucketing   = $feature['bucketing']    ?? '';
        $urlOverride = $feature['url_override'] ?? false;

        $this->name        = $name;
        $this->description = $description;
        $this->enabled     = new Enabled($enabled);
        $this->users       = new Users($users);
        $this->groups      = new Groups($groups);
        $this->sources     = new Sources($sources);
        $this->admin       = new Admin($admin);
        $this->internal    = new Internal($internal);
        $this->urlOverride = new UrlOverride($urlOverride);
        $this->excludeFrom = new ExcludeFrom($excludeFrom);
        $this->time        = new Time($start, $end);
        $this->bucketing   = new Bucketing($bucketing);
    }

    function name () : string
    { 
        return $this->name;
    }

    function enabled () : Enabled
    {
        return $this->enabled;
    }

    function description () : string
    {
        return $this->description;
    }

    function users () : Users
    {
        return $this->users;
    }

    function groups () : Groups
    {
        return $this->groups;
    }

    function sources () : Sources
    {
        return $this->sources;
    }

    function admin () : Admin
    {
        return $this->admin;
    }

    function internal () : Internal
    {
        return $this->internal;
    }

    function urlOverride () : UrlOverride
    {
        return $this->urlOverride;
    }

    function excludeFrom () : ExcludeFrom
    {
        return $this->excludeFrom;
    }

    function time () : Time
    {
        return $this->time;
    }

    function bucketing () : Bucketing
    {
        return $this->bucketing;
    }
}
