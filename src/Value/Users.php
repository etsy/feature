<?php

declare(strict_types=1);

namespace PabloJoan\Feature\Value;

/**
 * Parse the value of the 'users' properties of the feature's config stanza,
 * returning an array mappinng the user names to the variant they should see.
 */
class Users
{
    private $users = [];

    function __construct (array $stanza)
    {
        foreach ($stanza as $variant => $users) {
            if (!is_array($users)) $users = [$users];
            foreach ($users as $user) $this->users[$user] = $variant;
        }
    }

    function variant (User $user) : string
    {
        return $this->users[$user->id()] ?? '';
    }
}
