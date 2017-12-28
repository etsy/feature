<?php

declare(strict_types=1);

namespace PabloJoan\Feature\Value;

class CalculateBucketingId
{
    private $user;
    private $bucketing = 'random';

    function __construct (User $user, Bucketing $bucketing)
    {
        $this->user = $user;
        $this->bucketing = (string) $bucketing;
    }

    function id () : BucketingId
    {
        if ($this->bucketing === 'user' && !$this->user->id()) {
            $error = 'user id must be provided if user bucketing is enabled.';
            throw new \Exception($error);
        }

        if ($this->bucketing === 'uaid' && !$this->user->uaid()) {
            $error = 'user uaid must be provided if uaid bucketing is enabled.';
            throw new \Exception($error);
        }

        if ($this->bucketing === 'user') {
            return new BucketingId($this->user->id());
        }

        if ($this->bucketing === 'uaid') {
            return new BucketingId($this->user->uaid());
        }

        if (!$this->user->uaid()) return new BucketingId('no uaid');

        return new BucketingId($this->user->uaid());
    }
}
