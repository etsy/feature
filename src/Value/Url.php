<?php

declare(strict_types=1);

namespace PabloJoan\Feature\Value;

class Url
{
    private $features;

    function __construct (string $url)
    {
        $url = filter_var($url, FILTER_VALIDATE_URL);
        $url = $url ? parse_url($url, PHP_URL_QUERY) : '';
        $url = $url ? html_entity_decode($url) : '';

        $query  = [];
        foreach (explode('&', $url) as $val) {
            $x = explode('=', $val);
            $query[$x[0]] = $x[1] ?? '';
        }

        foreach (explode(',', $query['feature'] ?? '') as $feature) {
            $parts = explode(':', $feature);
            $this->features[$parts[0]] = $parts[1] ?? Variant::ON;
        }
    }

    function variant (string $name) : string
    {
        return $this->features[$name] ?? '';
    }
}
