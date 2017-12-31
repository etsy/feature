<?php

declare(strict_types=1);

namespace PabloJoan\Feature\Contract;

interface Sources
{
    function __construct (array $stanza);

    function variant (Source $source) : string;
}
