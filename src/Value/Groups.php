<?php

declare(strict_types=1);

namespace PabloJoan\Feature\Value;

/**
 * Parse the value of the 'groups' properties of the feature's config stanza,
 * returning an array mappinng the group names to the variant they should see.
 */
class Groups
{
    private $groups = [];

    function __construct (array $stanza)
    {
        foreach ($stanza as $variant => $groups) {
            foreach ((array) $groups as $group) {
                $this->groups[$group] = $variant;
            }
        }
    }

    function variant (User $user) : string
    {
        return $this->groups[$user->group()] ?? '';
    }
}
