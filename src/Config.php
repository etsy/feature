<?php

declare(strict_types=1);

namespace PabloJoan\Feature;

use PabloJoan\Feature\Value\CalculateBucketingId;
use PabloJoan\Feature\Contract\{ User, Url, Source, Feature, BucketingId };

class Config
{
    private $user;
    private $url;
    private $source;

    function __construct (User $user, Url $url, Source $source)
    {
        $this->user = $user;
        $this->url = $url;
        $this->source = $source;
    }

    /**
     * Is this feature enabled for the default id and the logged in user, if
     * any?
     */
    function isEnabled (Feature $feature) : bool
    {
        $id = (new CalculateBucketingId($this->user, $feature->bucketing()))->id();
        return $this->chooseVariant($feature, $id) !== 'off';
    }

    /**
     * What variant is enabled for the default id and the logged in user, if
     * any?
     */
    function variant (Feature $feature) : string
    {
        $id = (new CalculateBucketingId($this->user, $feature->bucketing()))->id();
        $variant = $this->chooseVariant($feature, $id);
        return $variant !== 'off' ? $variant : '';
    }

    /**
     * Is this feature enabled, bucketing on the given bucketing ID? (Other
     * methods of enabling a feature and specifying a variant such as users,
     * groups, and query parameters, will still work.)
     */
    function isEnabledBucketingBy (Feature $feature, BucketingId $id) : bool
    {
        return $this->chooseVariant($feature, $id) !== 'off';
    }

    /**
     * What variant is enabled, bucketing on the given bucketing ID, if any?
     */
    function variantBucketingBy (Feature $feature, BucketingId $id) : string
    {
        $variant = $this->chooseVariant($feature, $id);
        return $variant !== 'off' ? $variant : '';
    }

    /**
     * Get the name of the variant we should use. Returns OFF if the feature is
     * not enabled for $id.
     *
     * BucketingId $id - the id used to assign a variant based on the percentage
     * of users that should see different variants.
     */
    private function chooseVariant (Feature $feature, BucketingId $id) : string
    {
        $variant = $this->variantFromURL($feature);
        if ($variant) return $variant;

        $variant = $this->variantTime($feature);
        if ($variant) return $variant;

        $variant = $this->variantExcludedFrom($feature);
        if ($variant) return $variant;

        $variant = $this->variantForUser($feature);
        if ($variant) return $variant;

        $variant = $this->variantForGroup($feature);
        if ($variant) return $variant;

        $variant = $this->variantForSource($feature);
        if ($variant) return $variant;

        $variant = $this->variantForInternal($feature);
        if ($variant) return $variant;

        $variant = $this->variantForAdmin($feature);
        if ($variant) return $variant;

        $variant = $this->variantByPercentage($feature, $id);
        if ($variant) return $variant;

        return 'off';
    }

    /**
     * If the feature has public_url_override set to true, a specific variant
     * can be specified in the 'features' query parameter. In all other cases
     * return nothing, meaning nothing was specified. Note that foo:off will
     * turn off the 'foo' feature.
     */
    private function variantFromURL (Feature $feature) : string
    {
        $publicUrlOverride = $feature->publicUrlOverride();
        return $publicUrlOverride->variant($feature->name(), $this->url);
    }

    /**
     * Get the variant this user should see, if one was configured, none
     * otherwise.
     */
    private function variantForUser (Feature $feature) : string
    {
        return $feature->users()->variant($this->user);
    }

    /**
     * Get the variant visitor should see based on group they're currently
     * viewing.
     */
    private function variantForSource (Feature $feature) : string
    {
        return $feature->sources()->variant($this->source);
    }

    /**
     * Get the variant this user should see based on their group memberships, if
     * one was configured, none otherwise. N.B. If the user is in multiple
     * groups that are configured to see different variants, they'll get the
     * variant for one of their groups but there's no saying which one. If this
     * is a problem in practice we could make the configuration more complex. Or
     * you can just provide a specific variant via the 'users' property.
     */
    private function variantForGroup (Feature $feature) : string
    {
        return $feature->groups()->variant($this->user);
    }

    /**
     * What variant, if any, should we return if the current user is an admin.
     */
    private function variantForAdmin (Feature $feature) : string
    {
        return $feature->admin()->variant($this->user);
    }

    /**
     * What variant, if any, should we return for internal requests.
     */
    private function variantForInternal (Feature $feature) : string
    {
        return $feature->internal()->variant($this->user);
    }

    /**
     * Is this user excluded from seeing this feature because of their location?
     */
    private function variantExcludedFrom (Feature $feature) : string
    {
        return $feature->excludeFrom()->variant($this->user);
    }

    /**
     * Is this feature within the enabled time it was configured?
     */
    private function variantTime (Feature $feature) : string
    {
        return $feature->time()->variant();
    }

    /**
     * Finally, the normal case: use the percentage of users who should see each
     * variant to map a random-ish number to a particular variant.
     */
    private function variantByPercentage (Feature $feature, BucketingId $id) : string
    {
        $n = 100 * $this->randomish($feature, $id);
        foreach ($feature->enabled()->percentages() as $variant => $percent) {
            if ($n < $percent) return $variant;
        }
        return '';
    }

    /**
     * A random-ish number between 0 and 1 based on the feature name and $id
     * unless we are bucketing completely at random
     */
    private function randomish (Feature $feature, BucketingId $id) : float
    {
        if ((string) $feature->bucketing() === 'random') {
            return random_int(0, PHP_INT_MAX - 1) / PHP_INT_MAX;
        }
        /**
         * Map a hex value to the half-open interval bewtween 0 and 1 while
         * preserving uniformity of the input distribution.
         */
        $id = hash('sha256', $feature->name() . "-$id");
        $x = 0;
        for ($i = 0; $i < 30; ++$i) {
            $x = ($x << 1) + (hexdec($id[$i]) < 8 ? 0 : 1);
        }

        return $x / 1073741824; // $x / ( 1 << 30 )
    }
}
