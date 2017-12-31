<?php

declare(strict_types=1);

namespace PabloJoan\Feature\Value;

use PabloJoan\Feature\Contract\{ User, Groups as GroupsContract };

/**
 * Parse the value of the 'groups' properties of the feature's config stanza,
 * returning an array mappinng the group names to the variant they should see.
 */
class Groups implements GroupsContract
{
    private $groups = [];

    function __construct (array $stanza)
    {
        foreach ($stanza as $variant => $groups) {
            if (!is_array($groups)) $groups = [$groups];
            foreach ($groups as $group) $this->groups[$group] = $variant;
        }
    }

    function variant (User $user) : string
    {
        return $this->groups[$user->group()] ?? '';
    }
}
