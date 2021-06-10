<?php

declare(strict_types=1);

namespace PabloJoan\Feature;

use PabloJoan\Feature\Configurations\Collection;

/**
 * The public API testing whether a specific feature is enabled and, if so, what
 * variant should be used.
 *
 * Primary public API:
 *
 *   Feature->isEnabled(featureName: 'foo');
 *   Feature->variant(featureName: 'foo');
 *
 * For cases when we want to bucket on a user other than the currently logged in
 * user, on something else entirely (such as a shop ID), or any arbitrary
 * string, pass the string value as a second parameter.
 *
 *   Feature->isEnabled(featureName: 'foo', id: $id);
 *   Feature->variant(featureName: 'foo', id: $id);
 */
final class Feature
{
    private Collection $features;

    /**
     * @param array<string, array{enabled: int|array, bucketing?: string}> $features
     */
    public function __construct(array $features)
    {
        $this->features = new Collection(configurations: $features);
    }

    /**
     * Test whether the named feature is enabled for a given user
     * or arbitrary string.
     */
    public function isEnabled(string $featureName, string $id = ''): bool
    {
        return (bool) $this->variant(featureName: $featureName, id: $id);
    }

    /**
     * Get the name of the A/B variant for the named feature for
     * the given user or arbitrary string. Returns an empty string
     * if the feature is not enabled for $userId.
     */
    public function variant(string $featureName, string $id = ''): string
    {
        return $this->features
            ->get(featureName: $featureName)
            ->variantByPercentage(id: $id);
    }
}
