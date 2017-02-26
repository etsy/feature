<?php

namespace CafeMedia\Feature;

class Variant
{
    private $world;
    private $stanza;
    private $bucketingID;
    private $name = '';
    private $percentages = [];

    public function __construct(World $world)
    {
        $this->world = $world;
    }

    public function addStanza(Stanza $stanza)
    {
        $this->stanza = $stanza;
        //Put the enabled value into a more useful form
        //for actually doing bucketing.
        $total = 0;
        foreach ($stanza->enabled as $variant => $percentage) {
            $total += $this->computePercantage($variant, $percentage, $total);
        }
        if (!($total > 100)) return $this;
        throw new \Exception("Total of percentages > 100: $total");
    }

    public function addBucketingID($bucketingID)
    {
        $this->bucketingID = $bucketingID;
        return $this;
    }

    public function addName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function __toString()
    {
        return $this->variantFromURL() ?:
               $this->variantForUser() ?:
               $this->variantForGroup() ?:
               $this->variantForViewingGroup() ?:
               $this->variantForSource() ?:
               $this->variantForAdmin() ?:
               $this->variantForInternal() ?:
               $this->variantExcludedFrom() ?:
               $this->variantTime() ?:
               $this->variantByPercentage() ?:
               'off';
    }

    /**
     * For internal requests or if the feature has public_url_override
     * set to true, a specific variant can be specified in the
     * 'features' query parameter. In all other cases return false,
     * meaning nothing was specified. Note that foo:off will turn off
     * the 'foo' feature.
     */
    private function variantFromURL()
    {
        if (!$this->stanza->publicUrlOverride &&
            !$this->world->isInternalRequest() &&
            !$this->world->isAdmin()
        ) {
            return false;
        }

        $urlFeatures = $this->world->urlFeatures();
        if (!$urlFeatures) return false;

        foreach (explode(',', $urlFeatures) as $f) {
            $parts = explode(':', $f);
            if ($parts[0] === $this->name) {
                return isset($parts[1]) ? $parts[1] : 'on';
            }
        }

        return false;
    }

    /**
     * Get the variant this user should see, if one was configured,
     * false otherwise.
     */
    private function variantForUser()
    {
        if (!$this->stanza->users) return false;

        $name = strtolower($this->world->userName());
        if (!isset($this->stanza->users[$name])) return false;
        return $this->stanza->users[$name];
    }

    /**
     * Get the variant visitor should see based on group
     * they're currently viewing
     */
    private function variantForViewingGroup()
    {
        foreach ($this->stanza->groups as $groupID => $variant) {
            if ($this->world->viewingGroup($groupID)) return $variant;
        }
        return false;
    }

    /**
     * Get the variant visitor should see based on group
     * they're currently viewing
     */
    private function variantForSource()
    {
        foreach ($this->stanza->sources as $source => $variant) {
            if ($this->world->isSource($source)) return $variant;
        }
        return false;
    }

    /**
     * Get the variant this user should see based on their group
     * memberships, if one was configured, false otherwise. N.B. If
     * the user is in multiple groups that are configured to see
     * different variants, they'll get the variant for one of their
     * groups but there's no saying which one. If this is a problem in
     * practice we could make the configuration more complex. Or you
     * can just provide a specific variant via the 'users' property.
     */
    private function variantForGroup()
    {
        foreach ($this->stanza->groups as $groupID => $variant) {
            if ($this->world->viewingGroup($groupID)) return $variant;
        }

        return false;
    }

    /**
     * What variant, if any, should we return if the current user is
     * an admin.
     */
    private function variantForAdmin()
    {
        if ($this->stanza->adminVariant && $this->world->isAdmin()) {
            return $this->stanza->adminVariant;
        }
        return false;
    }

    /**
     * What variant, if any, should we return for internal requests.
     */
    private function variantForInternal()
    {
        if ($this->stanza->internalVariant &&
            $this->world->isInternalRequest()
        ) {
            return $this->stanza->internalVariant;
        }
        return false;
    }

    private function variantExcludedFrom()
    {
        $excluded = $this->stanza->exludeFrom
            && (
                (
                    isset($this->stanza->exludeFrom['zips']) &&
                    in_array(
                        $this->world->zipcode(),
                        $this->stanza->exludeFrom['zips']
                    )
                )
                || (
                    isset($this->stanza->exludeFrom['regions']) &&
                    in_array(
                        $this->world->region(),
                        $this->stanza->exludeFrom['regions']
                    )
                )
                || (
                    isset($this->stanza->exludeFrom['countries']) &&
                    in_array(
                        $this->world->country(),
                        $this->stanza->exludeFrom['countries']
                    )
                )
            );
        return $excluded ? 'off' : false;
    }

    private function variantTime()
    {
        $time = time();
        if (($this->stanza->start && $this->stanza->start < $time) ||
            ($this->stanza->end && $this->stanza->end > $time)
        ) {
            return 'off';
        }
        return false;
    }

    /**
     * Finally, the normal case: use the percentage of users who
     * should see each variant to map a random-ish number to a
     * particular variant.
     */
    private function variantByPercentage()
    {
        $n = 100 * $this->randomish();
        foreach ($this->percentages as $v) {
            // === 100 check may not be necessary but I'm not good
            // enough numerical analyst to be sure.
            if ($n < $v[0] || $v[0] === 100) return $v[1];
        }
        return false;
    }

    /**
     * A random-ish number in [0, 1) based on the feature name and $id
     * unless we are bucketing completely at random
     */
    private function randomish()
    {
        if ($this->stanza->bucketing === 'random') {
            return mt_rand(0, mt_getrandmax() - 1) / mt_getrandmax();
        }
        /**
         * Map a hex value to the half-open interval [0, 1) while
         * preserving uniformity of the input distribution.
         */
        $id = hash('sha256', "{$this->name}-{$this->bucketingID}");
        $len = min(30, strlen($id));
        $v = 0;
        for ($i = 0; $i < $len; ++$i) {
            $v = ($v << 1) + (hexdec($id[$i]) < 8 ? 0 : 1);
        }

        return $v / (1 << $len);
    }

    /*
     * Returns an array of pairs with the first element of the pair
     * being the upper-boundary of the variants percentage and the
     * second element being the name of the variant.
     */
    private function computePercantage($variant, $percentage, $total)
    {
        if ((!is_numeric($percentage) && !is_array($percentage)) ||
            (is_numeric($percentage) && ($percentage < 0 || $percentage > 100))
        ) {
            throw new \Exception('Bad percentage '. json_encode($percentage));
        }
        if (is_numeric($percentage)) {
            $this->percentages[] = [$total + $percentage, $variant];
            return $percentage;
        }
        foreach ($percentage as $variant => $percent) {
            if (!is_numeric($percent) || $percent < 0 || $percent > 100) {
                throw new \Exception('Bad percentage '. json_encode($percent));
            }
            $total += $percent;
            $this->percentages[] = [$total, $variant];
        }
        return $total;
    }
}