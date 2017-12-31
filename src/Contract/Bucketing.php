<?php

declare(strict_types=1);

namespace PabloJoan\Feature\Contract;

interface Bucketing
{
    function __construct (string $bucketBy);

    function __toString () : string;
}
