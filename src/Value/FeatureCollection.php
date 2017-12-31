<?php

declare(strict_types=1);

namespace PabloJoan\Feature\Value;

use PabloJoan\Feature\Contract\{
    FeatureCollection as FeatureCollectionContract,
    Feature as FeatureContract,
    Name as NameContract
};

class FeatureCollection implements FeatureCollectionContract
{
    private $features = [];

    function __construct (array $features)
    {
        foreach ($features as $name => $feature) {
            $this->features[$name] = new Feature(new Name($name), $feature);
        }
    }

    function get (NameContract $name) : FeatureContract
    {
        return $this->features[(string) $name] ?? new Feature($name, []);
    }

    function change (NameContract $name, array $feature)
    {
        if (!isset($this->features[(string) $name])) {
            throw new \Exception("feature '$name' does not exist.");
        }

        $this->features[(string) $name] = new Feature($name, $feature);
    }

    function add (NameContract $name, array $feature)
    {
        if (isset($this->features[(string) $name])) {
            throw new \Exception("feature '$name' already exists.");
        }
        $this->features[(string) $name] = new Feature($name, $feature);
    }

    function remove (NameContract $name)
    {
        unset($this->features[(string) $name]);
    }
}
