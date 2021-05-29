<?php

declare(strict_types=1);

namespace PabloJoan\Feature\Configurations;

use PabloJoan\Feature\Bucketing\Type as BucketType;
use PabloJoan\Feature\Bucketing\Id as BucketId;
use PabloJoan\Feature\Bucketing\Random as BucketRandom;

/**
 * A feature that can be enabled, disabled, ramped up, and
 * A/B tested.
 */
final class Config
{
    /**
     * @var array<string, int>
     */
    private array $percentages;

    private BucketType $bucketing;

    /**
     * @param array{enabled: int|array, bucketing?: string} $config
     */
    public function __construct(string $featureName, array $config = ['enabled' => 0])
    {
        $this->percentages = $this->parseEnabled(featureName: $featureName, enabled: $config['enabled']);
        $this->bucketing = $this->parseBucketing(bucketing: $config['bucketing'] ?? 'random');
    }

    /**
     * The percentage of users who should see each variant to
     * map a random-ish number to a particular variant.
     */
    public function variantByPercentage(string $id): string
    {
        $number = $this->bucketing->randomIshNumber(idToHash: $id);
        $percentRange = fn (int $percent): bool => $number < $percent;

        $variant = key(array_filter($this->percentages, $percentRange));
        return $variant ? $variant : '';
    }

    /**
     * Parse the 'enabled' property of the feature's config stanza.
     * Returns the upper-boundary of the variants percentage.
     *
     * @param int|array<string,int> $enabled
     * @return array<string, int>
     */
    private function parseEnabled(string $featureName, int|array $enabled): array
    {
        $total = 0;
        $percentages = [];

        $enabled = is_int($enabled) ? [$featureName => $enabled] : $enabled;

        foreach ($enabled as $variant => $percent) {
            $total += $this->percentage(percent: $percent);
            $percentages[$variant] = $total;
        }

        asort($percentages, SORT_NUMERIC);

        return $percentages;
    }

    /**
     * Parse the 'bucketing' property of the feature's config stanza.
     * Determines how the variants will be bucketed.
     */
    private function parseBucketing(string $bucketing): BucketType
    {
        return match ($bucketing) {
            'random' => new BucketRandom(),
            'id' => new BucketId(),
            default => throw new \Exception("bucketing option: $bucketing not supported.")
        };
    }

    private function percentage(int $percent): int
    {
        return ($percent >= 0 && $percent <= 100) ? $percent : 0;
    }
}
