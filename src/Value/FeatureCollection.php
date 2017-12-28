<?php

declare(strict_types=1);

namespace PabloJoan\Feature\Value;

class FeatureCollection
{
    private $features = [];

    function __construct (array $features)
    {
        foreach ($features as $name => $feature) {
            $this->features[$name] = new Feature(new Name($name), $feature);
        }
    }

    function get (Name $name) : Feature
    {
        return $this->features[(string) $name];
    }

    function change (Name $name, array $feature)
    {
        if (!isset($this->features[(string) $name])) {
            throw new \Exception("feature '$name' does not exist.");
        }

        $this->features[(string) $name] = new Feature($name, $feature);
    }
}
