<?php

declare(strict_types=1);

namespace PabloJoan\Feature\Configurations;

final class Collection
{
    /**
     * @var Config[]
     */
    private array $configurations;

    /**
     * @param array<string, array{enabled: int|array, bucketing?: string}> $configurations
     */
    public function __construct(array $configurations)
    {
        foreach ($configurations as $featureName => $config) {
            $this->configurations[$featureName] = new Config(featureName: $featureName, config: $config);
        }
    }

    public function get(string $featureName): Config
    {
        return $this->configurations[$featureName];
    }
}
