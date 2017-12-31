<?php

declare(strict_types=1);

namespace PabloJoan\Feature\Value;

use PabloJoan\Feature\Contract\BucketingId as BucketingIdContract;

class BucketingId implements BucketingIdContract
{
    private $id = '';

    function __construct (string $id)
    {
        if (!$id) throw new \Exception('a bucketing ID must be provided.');
        $this->id = $id;
    }

    function __toString () : string { return $this->id; }
}
