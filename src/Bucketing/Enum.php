<?php

declare(strict_types=1);

namespace PabloJoan\Feature\Bucketing;

enum Enum : string
{
    case RANDOM = 'random';
    case ID     = 'id';

    public function getBucketingClass(): Type
    {
        return match ($this) {
            static::RANDOM => new Random(),
            static::ID     => new Id()
        };
    }
}
