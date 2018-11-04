<?php

declare(strict_types=1);

namespace PabloJoan\Feature\Value;

/**
 * Parse the 'enabled' property of the feature's config stanza.
 * Returns the upper-boundary of the variants percentage.
 */
class Enabled
{
    private $percentages;

    function __construct ($enabled)
    {
        $total = 0;
        foreach ((array) $enabled as $variant => $percent) {
            $total += $this->percentage($percent);
            $variant = is_int($variant) ? Variant::ON : $variant;
            $this->percentages[$variant] = $total;
        }
        asort($this->percentages, SORT_NUMERIC);
    }

    function variantByPercentage (float $number) : string
    {
        $threshHold = function (int $percent) use ($number) : bool {
            return $number < $percent;
        };

        $variant = key(array_filter($this->percentages, $threshHold));

        return (string) ($variant ?: '');
    }

    private function percentage (int $percent) : int
    {
        return ($percent >= 0 && $percent <= 100) ? $percent : 0;
    }
}
