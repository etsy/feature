<?php

declare(strict_types=1);

namespace PabloJoan\Feature\Contract;

interface Feature
{
    function __construct (Name $name, array $feature);

    function name () : Name;

    function enabled () : Enabled;

    function description () : Description;

    function users () : Users;

    function groups () : Groups;

    function sources () : Sources;

    function admin () : Admin;

    function internal () : Internal;

    function publicUrlOverride () : PublicUrlOverride;

    function excludeFrom () : ExcludeFrom;

    function time () : Time;

    function bucketing () : Bucketing;
}
