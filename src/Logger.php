<?php

namespace CafeMedia\Feature;

use Psr\Log\LoggerInterface;

/**
 * Logging -- for each feature that is checked we can log that it was
 * checked, what variant was choosen, and why.
 *
 * Class Logger
 * @package CafeMedia\Feature
 */
class Logger
{
    private $logger;

    /**
     * Logger constructor.
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Log that the feature $name was checked with $variant selected
     * by $selector. This is only called once per feature/bucketing id
     * per request.
     *
     * @param $name
     * @param $variant
     * @param $selector
     */
    public function log ($name, $variant, $selector = '')
    {
        $this->logger->info("AB: $name=$variant selector:$selector");
    }

    /**
     * @param $message
     */
    public function error($message)
    {
        $this->logger->error($message);
    }
}
