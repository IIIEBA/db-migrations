<?php

declare(strict_types=1);

namespace DbMigrations\Kernel\Util;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Class LoggerTrait
 * @package Kernel\Util
 */
trait LoggerTrait
{
    /**
     * The logger instance.
     *
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Sets a logger.
     *
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger = null)
    {
        $this->logger = $logger === null ? new NullLogger() : $logger;
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }
}
