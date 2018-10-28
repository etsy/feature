<?php

declare(strict_types=1);

namespace PabloJoan\Feature\Value;

class CalculateBucketingId
{
    private $user;
    private $bucketing;

    function __construct (User $user, Bucketing $bucketing)
    {
        $this->user = $user;
        $this->bucketing = $bucketing;
    }

    function id () : string
    {
        $id = '';
        switch ($this->bucketing->by()) {
            case Bucketing::USER:
                $id = $this->user->id();
                break;

            case Bucketing::UAID:
                $id = $this->user->uaid();
                break;
    
            case Bucketing::RANDOM:
                $id = $this->user->uaid() ? $this->user->uaid() : 'no uaid';
                break;
        }

        return $id;
    }
}
