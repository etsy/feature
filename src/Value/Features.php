<?php

declare(strict_types=1);

namespace PabloJoan\Feature\Value;

class Features implements \ArrayAccess
{
    private $features = [];

    function __construct (array $features)
    {
        foreach ($features as $name => $feature) {
            $this->features[$name] = new Feature($name, $feature);
        }
    }

    function offsetSet ($name, $feature)
    {
        $this->features[$name] = new Feature($name, $feature);
    }

    function offsetExists ($name)
    {
        return isset($this->features[$name]);
    }

    function offsetUnset ($name)
    {
        unset($this->features[$name]);
    }

    function offsetGet ($name) : Feature
    {
        return $this->features[$name] ?? new Feature($name, []);
    }
}
