<?php

namespace Elements\Bundle\ExportToolkitBundle\Traits;

use Pimcore\Log\ApplicationLogger;
use Psr\Log\LoggerInterface;

/**
 * Same as the LoggerAwareTrait shipped by PSR-3, but doesn't type hint LoggerInterface as the application logger
 * can't implement the interface for BC.
 */
trait LoggerAwareTrait
{
    /**
     * @var LoggerInterface|ApplicationLogger
     */
    protected $logger;

    /**
     * @return ApplicationLogger|LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @param ApplicationLogger|LoggerInterface $logger
     * @return $this
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;

        return $this;
    }
}
