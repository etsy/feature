<?php

/*
 * The interface implemented by classes that can be returned by
 * Feature_World->unit().
 */
interface Feature_ExperimentalUnit {

    /*
     * Return the specially assigned variant, if any, due to any of
     * the clauses in the config stanza or false otherwise.
     */
    public function assignedVariant($data, $config);

    /*
     * Get the bucketing id for this experimental unit based on the
     * configured scheme. (I.e. the string value of the 'bucketing'
     * clause of the config stanza.) This ID should be stable and
     * unique: it shouldn't change and no two distinct experimental
     * units should have the same bucketing ID. (The latter
     * requirement is because we cache the results of explicit variant
     * assignmnts under the bucktingID.)
     */
    public function bucketingID ($data, $scheme);

    /*
     * What is the name of the default bucketing scheme for this kind
     * of experimental unit?
     */
    public function defaultBucketing();

}
