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
     * @var int[]
     */
    private array $percentages;

    private BucketType $bucketing;

    /**
     * @param array{enabled: int|array, bucketing?: string} $config
     */
    public function __construct(array $config = ['enabled' => 0])
    {
        $bucketing = $config['bucketing'] ?? 'random';
        $this->bucketing = match ($bucketing) {
            'random' => new BucketRandom(),
            'id' => new BucketId(),
            default => throw new \Exception("bucketing option: $bucketing not supported.")
        };

        $this->parseEnabled(enabled: $config['enabled'] ?? 0);
    }

    /**
     * The percentage of users who should see each variant to
     * map a random-ish number to a particular variant.
     */
    public function variantByPercentage(string $id): string
    {
        $number = $this->bucketing->randomIshNumber(idToHash: $id);

        $percentRange = fn (int $percent): bool => $number < $percent;

        $variant = (string) key(array_filter($this->percentages, $percentRange));

        return ($variant || $variant === '0') ? $variant : '';
    }

    /**
     * Parse the 'enabled' property of the feature's config stanza.
     * Returns the upper-boundary of the variants percentage.
     *
     * @param int|array<string,int> $enabled
     */
    private function parseEnabled(int|array $enabled): void
    {
        $total = 0;
        foreach ((array) $enabled as $variant => $percent) {
            $total += $this->percentage(percent: $percent);
            $this->percentages[(string)$variant] = $total;
        }
        asort($this->percentages, SORT_NUMERIC);
    }

    private function percentage(int $percent): int
    {
        return ($percent >= 0 && $percent <= 100) ? $percent : 0;
    }
}
