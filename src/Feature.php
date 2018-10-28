<?php

declare(strict_types=1);

namespace PabloJoan\Feature;

use PabloJoan\Feature\Value\{
    FeatureCollection,
    User,
    Url
};

/**
 * The public API testing whether a specific feature is enabled and, if so, what
 * variant should be used.
 *
 * Primary public API:
 *
 *   Feature->isEnabled('foo');
 *   Feature->variant('foo');
 *
 * For cases when we want to bucket on a user other than the currently logged in
 * user (e.g. to bucket how we treat listings by their owners) this secondary
 * API is available:
 *
 *   Feature->isEnabledFor('foo', $user);
 *   Feature->variantFor('foo', $user);
 *
 * And for case when we want to bucket on something else entirely (such as a
 * shop ID), we provide these two methods:
 *
 *   Feature->isEnabledBucketingBy('foo', $bucketingID);
 *   Feature->variantBucketingBy('foo', $bucketingID);
 */
class Feature
{
    private $features;
    private $user;
    private $url;
    private $source;

    function __construct (array $input = null)
    {
        $this->features = new FeatureCollection($input['features'] ?? []);
        $this->user = new User($input['user'] ?? []);
        $this->url = new Url($input['url'] ?? '');
        $this->source = $input['source'] ?? '';
    }

    /**
     * Replaces all features with a new set of features.
     */
    function changeFeatures (array $features) : Feature
    {
        $this->features = new FeatureCollection($features);
        return $this;
    }

    /**
     * Replaces one existing feature with a new feature config of the same name.
     * If feature does not exist, it adds one new feature config to the
     * collection of features.
     */
    function setFeature (string $name, array $feature) : Feature
    {
        $this->features->set($name, $feature);
        return $this;
    }

    /**
     * Removes one existing feature from the collection.
     */
    function removeFeature (string $name) : Feature
    {
        $this->features->remove($name);
        return $this;
    }

    /**
     * Replaces the user used to calculate variants.
     */
    function changeUser (array $user) : Feature
    {
        $this->user = new User($user);
        return $this;
    }

    /**
     * Replaces the url used to calculate variants.
     */
    function changeUrl (string $url) : Feature
    {
        $this->url = new Url($url);
        return $this;
    }

    /**
     * Replaces the source used to calculate variants.
     */
    function changeSource (string $source) : Feature
    {
        $this->source = $source;
        return $this;
    }

    /**
     * Test whether the named feature is enabled for the current user.
     */
    function isEnabled (string $name) : bool
    {
        $config = new Config($this->user, $this->url, $this->source);
        return $config->isEnabled($this->features->get($name));
    }

    /**
     * Test whether the named feature is enabled for a given user. This method
     * should only be used when we want to bucket based on a user other than the
     * current logged in user, e.g. if we are bucketing different listings based
     * on their owner.
     */
    function isEnabledFor (string $name, array $user) : bool
    {
        $config = new Config(new User($user), $this->url, $this->source);
        return $config->isEnabled($this->features->get($name));
    }

    /**
     * Test whether the named feature is enabled for a given arbitrary string.
     * This method should only be used when we want to bucket based on something
     * other than a user, e.g. shops, teams, treasuries, tags, etc.
     */
    function isEnabledBucketingBy (string $name, string $id) : bool
    {
        $config = new Config(new User([]), $this->url, $this->source); 
        return $config->isEnabledBucketingBy(
            $this->features->get($name),
            $id
        );
    }

    /**
     * Get the name of the A/B variant for the named feature for the current
     * user.
     */
    function variant (string $name) : string
    {
        $config = new Config($this->user, $this->url, $this->source);
        return $config->variant($this->features->get($name));
    }

    /**
     * Get the name of the A/B variant for the named feature for the given user.
     * This method should only be used when we want to bucket based on a user
     * other than the current logged in user, e.g. if we are bucketing different
     * listings based on their owner.
     */
    function variantFor (string $name, array $user) : string
    {
        $config = new Config(new User($user), $this->url, $this->source);
        return $config->variant($this->features->get($name));
    }

    /**
     * Get the name of the A/B variant for the named feature, bucketing by the
     * given bucketing ID. (For other checks such as admin, and user whitelists
     * uses the current user which may or may not make sense. If it doesn't
     * make sense, don't configure the feature to use those mechanisms.)
     */
    function variantBucketingBy (string $name, string $id) : string
    {
        $config = new Config(new User([]), $this->url, $this->source);
        return $config->variantBucketingBy(
            $this->features->get($name),
            $id
        );
    }

    function description (string $name) : string
    {
        return (string) $this->features->get($name)->description();
    }
}
