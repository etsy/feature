<?php

declare(strict_types=1);

namespace PabloJoan\Feature\Contract;

interface FeatureCollection
{
    function __construct (array $features);

    function get (Name $name) : Feature;

    function change (Name $name, array $feature);

    function add (Name $name, array $feature);

    function remove (Name $name);
}
