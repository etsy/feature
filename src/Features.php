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
 *   Feature->getEnabledVariant(featureName: 'foo', id: $id);
 */
final readonly class Features
{
    private Collection $features;

    public function __construct(array $features)
    {
        $this->features = new Collection($features);
    }

    /**
     * Test whether the named feature is enabled for a given user
     * or arbitrary string.
     */
    public function isEnabled(string $featureName, string $id = ''): bool
    {
        return (bool) $this->getEnabledVariant(
            featureName: $featureName,
            id: $id
        );
    }

    /**
     * Get the name of the enabled variant for the named feature for the given
     * id. Returns an empty string if the feature is not enabled.
     */
    public function getEnabledVariant(
        string $featureName,
        string $id = ''
    ): string
    {
        return $this->features->get($featureName)->getEnabledVariant($id);
    }
}
