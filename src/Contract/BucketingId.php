<?php

declare(strict_types=1);

namespace PabloJoan\Feature\Contract;

interface BucketingId
{
    function __construct (string $id);

    function __toString () : string;
}
