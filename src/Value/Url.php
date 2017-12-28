<?php

declare(strict_types=1);

namespace PabloJoan\Feature\Value;

class Url
{
    private $features = '';

    function __construct (string $url)
    {
        if (!$url) return;

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new \Exception("$url is not a valid url.");
        }

        $url = parse_url($url, PHP_URL_QUERY);
        if (!$url) return;

        $query  = [];
        foreach (explode('&', html_entity_decode($url)) as $val) {
            $x = explode('=', $val);
            $query[$x[0]] = $x[1] ?? '';
        }

        $this->features = $query['feature'] ?? '';
    }

    function variant (Name $name) : string
    {
        $name = (string) $name;

        foreach (explode(',', $this->features) as $feature) {
            $parts = explode(':', $feature);
            if ($parts[0] === $name) return $parts[1] ?? 'on';
        }

        return '';
    }
}
