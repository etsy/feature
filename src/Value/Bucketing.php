<?php

declare(strict_types=1);

namespace PabloJoan\Feature\Value;

class Bucketing
{
    private $by;

    const RANDOM = 0;
    const UAID   = 1;
    const USER   = 2;

    function __construct (string $bucketBy)
    {
        switch ($bucketBy) {
            case 'random':
                $bucketBy = self::RANDOM;
                break;

            case 'uaid':
                $bucketBy = self::RANDOM;
                break;

            case 'user':
                $bucketBy = self::RANDOM;
                break;
            
            default:
                $bucketBy = self::RANDOM;
                break;
        }

        $this->by = $bucketBy;
    }

    function by () : int
    {
        return $this->by;
    }
}
