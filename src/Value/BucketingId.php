<?php

declare(strict_types=1);

namespace PabloJoan\Feature\Value;

class BucketingId
{
    private $id = '';

    function __construct (string $id)
    {
        if (!$id) throw new \Exception('a bucketing ID must be provided.');
        $this->id = $id;
    }

    function __toString () : string { return $this->id; }
}
