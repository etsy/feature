<?php

declare(strict_types=1);

namespace PabloJoan\Feature\Bucketing;

final readonly class Id implements Type
{
    /**
     * hexdec('ffffffff') is the largest possible outcome
     * of hash('crc32c', $idToHash)
     */
    private const int    TOTAL     = 4294967295;
    private const string HASH_ALGO = 'crc32c';

    /**
     * Convert Id string to a Hex
     * Convert Hex to Dec int
     * Get a percentage int
     */
    public function strToIntHash(string $idToHash): int
    {
        $hex = hash(self::HASH_ALGO, $idToHash);
        $dec = hexdec($hex);

        $x = (int) round($dec / self::TOTAL * 100);
        return $x === 100 ? 99 : $x;
    }
}
