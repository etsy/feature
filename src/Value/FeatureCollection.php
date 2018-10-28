<?php

declare(strict_types=1);

namespace PabloJoan\Feature\Value;

class FeatureCollection
{
    private $features = [];

    function __construct (array $features)
    {
        foreach ($features as $name => $feature) {
            $this->features[$name] = new Feature($name, $feature);
        }
    }

    function get (string $name) : Feature
    {
        return $this->features[$name] ?? new Feature($name, []);
    }

    function set (string $name, array $feature) : FeatureCollection
    {
        $this->features[$name] = new Feature($name, $feature);
        return $this;
    }

    function remove (string $name) : FeatureCollection
    {
        unset($this->features[$name]);
        return $this;
    }
}
