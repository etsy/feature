<?php

declare(strict_types=1);

namespace PabloJoan\Feature\Value;

class ExcludeFrom
{
    private $zips;
    private $regions;
    private $countries;

    function __construct (array $excludeFrom)
    {
        $zips = isset($excludeFrom['zips']) && \is_array($excludeFrom['zips']);

        $regions = isset($excludeFrom['regions']);
        $regions = $regions && \is_array($excludeFrom['regions']);

        $countries = isset($excludeFrom['countries']);
        $countries = $countries && \is_array($excludeFrom['countries']);

        $this->zips = $zips ? $excludeFrom['zips'] : [];
        $this->regions = $regions ? $excludeFrom['regions'] : [];
        $this->countries = $countries ? $excludeFrom['countries'] : [];
    }

    function variant (User $user) : string
    {
        $zips = \in_array($user->zipcode(), $this->zips, true);
        $regions = \in_array($user->region(), $this->regions, true);
        $countries = \in_array($user->country(), $this->countries, true);

        return $zips || $regions || $countries ? Variant::OFF : '';
    }
}
