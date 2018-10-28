<?php

declare(strict_types=1);

namespace PabloJoan\Feature\Value;

/**
 * Parse the value of the 'sources' properties of the feature's config stanza,
 * returning an array mappinng the source names to the variant they should see.
 */
class Sources
{
    private $sources;

    function __construct (array $stanza)
    {
        foreach ($stanza as $variant => $sources) {
            foreach ((array) $sources as $source) {
                $this->sources[$source] = $variant;
            }
        }
    }

    function variant (string $source) : string
    {
        return $this->sources[$source] ?? '';
    }
}
