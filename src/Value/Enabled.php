<?php

declare(strict_types=1);

namespace PabloJoan\Feature\Value;

/**
 * Parse the 'enabled' property of the feature's config stanza.
 * Returns the upper-boundary of the variants percentage.
 */
class Enabled
{
    private $percentages = [];

    function __construct ($enabled)
    {
        $this->checkValueType($enabled);

        if (is_int($enabled)) $enabled = ['on' => $enabled];

        $total = 0;
        foreach ($enabled as $variant => $percent) {
            $this->checkPercentage($percent);

            $total += $percent;
            $this->percentages[$variant] = $total;
        }

        if ($total <= 100) return;

        throw new \Exception("Total of percentages > 100: $total");
    }

    function percentages () : array { return $this->percentages; }

    private function checkValueType ($enabled)
    {
        if (is_int($enabled) || is_array($enabled)) return;

        $error = 'Malformed enabled property ' . json_encode($enabled);
        throw new \Exception($error);
    }

    private function checkPercentage (int $percent)
    {
        if ($percent >= 0 && $percent <= 100) return;
        throw new \Exception('Bad percentage ' . json_encode($percent));
    }
}
