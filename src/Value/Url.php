<?php

declare(strict_types=1);

namespace PabloJoan\Feature\Value;

use PabloJoan\Feature\Contract\{ Url as UrlContract, Name };

class Url implements UrlContract
{
    private $features = [];

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

        foreach (explode(',', $query['feature'] ?? '') as $feature) {
            $parts = explode(':', $feature);
            $this->features[$parts[0]] = $parts[1] ?? 'on';
        }
    }

    function variant (Name $name) : string
    {
        $name = (string) $name;

        foreach ($this->features as $feature => $variant) {
            if ($feature === $name) return $variant ?? 'on';
        }

        return '';
    }
}
