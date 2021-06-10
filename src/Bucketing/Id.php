<?php

declare(strict_types=1);

namespace PabloJoan\Feature\Bucketing;

final class Id implements Type
{
    /**
     * hexdec('ffffffff') is the largest possible outcome
     * of hash('crc32c', $idToHash)
     */
    private const TOTAL = 4294967295;

    /**
     * Convert Id string to a Hex
     * Convert Hex to Dec int
     * Get a percentage float
     */
    public function randomIshNumber(string $idToHash = ''): float
    {
        $hex = hash('crc32c', $idToHash);
        $dec = hexdec($hex);

        $x = $dec / self::TOTAL;
        return $x * 100;
    }
}
