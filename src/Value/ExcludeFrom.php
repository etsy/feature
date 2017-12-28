<?php

declare(strict_types=1);

namespace PabloJoan\Feature\Value;

class ExcludeFrom
{
    private $zips = [];
    private $regions = [];
    private $countries = [];

    function __construct (array $excludeFrom)
    {
        if (!$excludeFrom) return;

        $zips = isset($excludeFrom['zips']) && is_array($excludeFrom['zips']);

        $regions = isset($excludeFrom['regions']);
        $regions = $regions && is_array($excludeFrom['regions']);

        $countries = isset($excludeFrom['countries']);
        $countries = $countries && is_array($excludeFrom['countries']);

        if ($zips) $this->zips = $excludeFrom['zips'];
        if ($regions) $this->regions = $excludeFrom['regions'];
        if ($countries) $this->countries = $excludeFrom['countries'];

        if ($zips || $regions || $countries) return;

        $error = 'bad exclude_from stanza ' . json_encode($excludeFrom);
        throw new \Exception($error);
    }

    function variant (User $user) : string
    {
        $zips = in_array($user->zipcode(), $this->zips, true);
        $regions = in_array($user->region(), $this->regions, true);
        $countries = in_array($user->country(), $this->countries, true);

        return $zips || $regions || $countries ? 'off' : '';
    }
}
