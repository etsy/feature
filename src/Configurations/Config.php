<?php

declare(strict_types=1);

namespace PabloJoan\Feature\Configurations;

use PabloJoan\Feature\Bucketing\Enum as BucketOptions;
use PabloJoan\Feature\Bucketing\Type as BucketType;

/**
 * A feature that can be enabled, disabled, ramped up, and A/B tested.
 */
final readonly class Config
{
    private array      $variantIntegerRanges;
    private BucketType $bucketing;

    public function __construct(array $config)
    {
        $this->variantIntegerRanges = $this->calculateIntegerRangeFromVariants(
            $config['variants'] ?? []
        );

        $bucketingOption = BucketOptions::tryFrom($config['bucketing'] ?? '');
        $bucketingOption ??= BucketOptions::RANDOM;
        $this->bucketing = $bucketingOption->getBucketingClass();
    }

    /**
     * Using a random 0 - 100 number or a 0 - 100 number hashed from an id,
     * Select the variant where this random or hashed integer falls within it's
     * calculated integer range.
     */
    public function pickVariantOutOfHat(string $id): string
    {
        $hashOrRandomNumber = $this->bucketing->strToIntHash($id);

        foreach ($this->variantIntegerRanges as $variant => $variantRange) {
            if ($hashOrRandomNumber < $variantRange) {
                return $variant;
            }
        }

        return '';
    }

    /**
     * Parse the 'variants' property of the feature's config stanza.
     * Returns the upper-boundary of the variants percentage and uses that
     * upper-boundary integer as its range of integers.
     */
    private function calculateIntegerRangeFromVariants(array $variants): array
    {
        $total = 0;
        $percentages = [];

        foreach ($variants as $variant => $percent) {
            $total += $percent;
            $percentages[$variant] = $total;
        }

        asort($percentages, SORT_NUMERIC);

        return $percentages;
    }
}
