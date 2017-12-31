<?php

declare(strict_types=1);

namespace PabloJoan\Feature\Value;

use PabloJoan\Feature\Contract\Bucketing as BucketingContract;

class Bucketing implements BucketingContract
{
    private $by = 'random';

    function __construct (string $bucketBy)
    {
        $this->by = $bucketBy;

        if (in_array($bucketBy, ['random', 'uaid', 'user'], true)) return;

        $error = 'bucketing must be either "random", "uaid" or "user". ';
        $error .= $bucketBy;
        throw new \Exception($error);
    }

    function __toString () : string { return $this->by; }
}
