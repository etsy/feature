<?php

declare(strict_types=1);

namespace PabloJoan\Feature\Configurations;

final readonly class Collection
{
    private array $configurations;

    public function __construct(array $configurations)
    {
        $this->configurations = array_map(
            $this->buildConfig(...),
            $configurations
        );
    }

    public function get(string $featureName): Config
    {
        return $this->configurations[$featureName];
    }

    private function buildConfig(array $config): Config
    {
        return new Config($config);
    }
}
