<?php

declare(strict_types=1);

namespace PabloJoan\Feature\Contract;

interface Enabled
{
    function __construct ($enabled);

    function percentages () : array;
}
