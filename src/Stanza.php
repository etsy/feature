<?php

namespace CafeMedia\Feature;

class Stanza
{
    private $description = '';
    private $enabled = [];
    private $users = [];
    private $groups = [];
    private $sources = [];
    private $adminVariant;
    private $internalVariant;
    private $publicUrlOverride;
    private $bucketing;
    private $exludeFrom;
    private $start = false;
    private $end = false;

    public function __construct(array $stanza)
    {
        // Pull stuff from the config stanza.
        $this->description = $this->parseDescription($stanza);
        $this->enabled = $this->parseEnabled($stanza);
        $this->users = $this->parseUsersOrGroups($stanza, 'users');
        $this->groups = $this->parseUsersOrGroups($stanza, 'groups');
        $this->sources = $this->parseUsersOrGroups($stanza, 'sources');
        $this->adminVariant = $this->parseVariantName($stanza, 'admin');
        $this->internalVariant = $this->parseVariantName($stanza, 'internal');
        $this->publicUrlOverride = $this->parsePublicURLOverride($stanza);
        $this->bucketing = $this->parseBucketBy($stanza);
        $this->exludeFrom = $this->parseExcludeFrom($stanza);
        $this->start = $this->parseStart($stanza);
        $this->end = $this->parseEnd($stanza);
    }

    public function __get($name)
    {
        if (isset($this->$name)) return $this->$name;
        throw new \Exception("$name is not a property of the Stanza class");
    }

    ////////////////////////////////////////////////////////////////////////
    // Configuration parsing

    private function parseDescription(array $stanza)
    {
        if (isset($stanza['description'])) return $stanza['description'];
        return 'No description.';
    }

    /**
     * Parse the 'enabled' property of the feature's config stanza.
     */
    private function parseEnabled(array $stanza)
    {
        $enabled = 0;
        if (isset($stanza['enabled'])) $enabled = $stanza['enabled'];
        if (!is_numeric($enabled) && !is_array($enabled)) {
            throw new \Exception(
                'Malformed enabled property ' . json_encode($stanza)
            );
        }
        if (is_numeric($enabled) && $enabled < 0) {
            throw new \Exception("enabled ($enabled) < 0");
        }
        if (is_numeric($enabled) && $enabled > 100) {
            throw new \Exception("enabled ($enabled) > 0");
        }
        return ['on' => $enabled];
    }

    /**
     * Parse the value of the 'users' and 'groups' properties of the
     * feature's config stanza, returning an array mappinng the user
     * or group names to they variant they should see.
     */
    private function parseUsersOrGroups(array $stanza, $what)
    {
        $value = false;
        if (isset($stanza[$what])) $value = $stanza[$what];
        if (is_string($value) || is_numeric($value)) {
            // Users are configrued with their user names. Groups as
            // numeric ids. (Not sure if that's a great idea.)
            return [$value => 'on'];
        }

        $result = [];
        /**
         * Is the given object an array value that could have been created
         * with array(...) with no =>'s in the ...?
         */
        if (!is_array($value)) return $result;
        if (array_keys($value) === range(0, count($value) - 1)) {
            foreach ($value as $who) $result[strtolower($who)] = 'on';
            return $result;
        }

        $badKeys = false;
        if (is_array($this->enabled)) {
            $badKeys = array_keys(array_diff_key($value, $this->enabled));
        }
        if ($badKeys) {
            throw new \Exception("Unknown variants " . implode(', ', $badKeys));
        }

        foreach ($value as $variant => $whos) {
            if (!is_array($whos)) $whos = [$whos];
            foreach ($whos as $who) $result[strtolower($who)] = $variant;
        }

        return $result;
    }

    /**
     * Parse the variant name value for the 'admin' and 'internal'
     * properties. If non-falsy, must be one of the keys in the
     * enabled map unless enabled is 'on' or 'off'.
     */
    private function parseVariantName(array $stanza, $what)
    {
        $value = false;
        if (isset($stanza[$what])) $value = $stanza[$what];
        if (!$value) return false;

        if (!is_array($this->enabled) || isset($this->enabled['on'][$value])) {
            return $value;
        }

        throw new \Exception(
            "Unknown variant $value " . json_encode($this->enabled)
        );
    }

    private function parsePublicURLOverride(array $stanza)
    {
        if (!isset($stanza['public_url_override'])) return false;
        return $stanza['public_url_override'];
    }

    private function parseBucketBy(array $stanza)
    {
        if (isset($stanza['bucketing'])) return $stanza['bucketing'];
        return 'uaid';
    }

    private function parseExcludeFrom(array $stanza)
    {
        if (!isset($stanza['exclude_from'])) return false;

        if (is_array($stanza['exclude_from']) &&
            (isset($stanza['exclude_from']['zips']) ||
             isset($stanza['exclude_from']['region']) ||
             isset($stanza['exclude_from']['country']))
        ) {
            return $stanza['exclude_from'];
        }

        throw new \Exception('bad exclude_from stanza' . json_encode($stanza));
    }

    private function parseStart(array $stanza)
    {
        if (!isset($stanza['start'])) return false;
        $time = strtotime($stanza['start']);
        if ($time) return $time;
        throw new \Exception("{$stanza['start']} is not a valid time format");
    }

    private function parseEnd(array $stanza)
    {
        if (!isset($stanza['end'])) return false;
        $time = strtotime($stanza['end']);
        if ($time) return $time;
        throw new \Exception("{$stanza['end']} is not a valid time format");
    }
}