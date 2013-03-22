<?php

/**
 * An example of a simple experimental unit, in this case for treating
 * search queries as the experimental unit. E.g. we have a new search
 * algorithm and we want to use it for x% of all searches except we
 * want the assignment of algorithm to search string to be stable. It
 * also allows us to configure the variant for specific queries via a
 * 'queries' clause analogous to 'users' and 'groups'.
 */
class Feature_EtsySearchQueryUnit implements Feature_ExperimentalUnit {

    public function assignedVariant($query, $config) {
        return $this->variantForQuery($query, $config->getListy('queries'));
    }

    public function bucketingID ($query, $scheme) {
        return $query;
    }

    public function defaultBucketing() {
        return 'id';
    }

}
