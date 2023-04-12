<?php

declare(strict_types=1);

namespace PabloJoan\Feature\Bucketing;

final readonly class Id implements Type
{
    /**
     * hexdec('ffffffff') is the largest possible outcome
     * of hash('crc32c', $idToHash)
     */
    private const TOTAL     = 4294967295;
    private const HASH_ALGO = 'crc32c';

    /**
     * Convert Id string to a Hex
     * Convert Hex to Dec int
     * Get a percentage float
     */
    public function strToIntHash(string $idToHash = ''): float
    {
        $hex = hash(self::HASH_ALGO, $idToHash);
        $dec = hexdec($hex);

        $x = $dec / self::TOTAL;
        return $x * 100;
    }
}
