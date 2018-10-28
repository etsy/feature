<?php

declare(strict_types=1);

namespace PabloJoan\Feature\Value;

class Time
{
    private $start;
    private $end;

    function __construct (string $start, string $end)
    {
        $start = strtotime($start);
        $this->start = $start ? $start : 0;

        $end = strtotime($end);
        $this->end = $end ? $end : 0;
    }

    function variant () : string
    {
        $time = time();

        $startNotValid = $this->start && $this->start > $time;
        $endNotValid = $this->end && $this->end < $time;

        return $startNotValid || $endNotValid ? Variant::OFF : '';
    }
}
