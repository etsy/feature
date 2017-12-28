<?php

declare(strict_types=1);

namespace PabloJoan\Feature\Value;

class Time
{
    private $start = 0;
    private $end = 0;

    function __construct (string $start, string $end)
    {
        if ($start) $this->start = $this->timeValue($start);
        if ($end) $this->end = $this->timeValue($end);
    }

    function variant () : string
    {
        $time = time();

        $startNotValid = $this->start && $this->start > $time;
        $endNotValid = $this->end && $this->end < $time;

        return $startNotValid || $endNotValid ? 'off' : '';
    }

    private function timeValue (string $time) : int
    {
        $time = strtotime($time);
        if (!$time) throw new \Exception("$time is not a valid time format");
        return $time;
    }
}
