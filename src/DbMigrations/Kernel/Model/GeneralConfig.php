<?php

declare(strict_types=1);

namespace DbMigrations\Kernel\Model;

use BaseExceptions\Exception\InvalidArgument\EmptyStringException;
use BaseExceptions\Exception\InvalidArgument\NotStringException;

/**
 * Class GeneralConfig
 * @package Kernel\Model
 */
class GeneralConfig implements GeneralConfigInterface
{
    /**
     * @var string
     */
    private $dbFolderPath;

    /**
     * GeneralConfig constructor.
     *
     * @param string $dbFolderPath
     */
    public function __construct($dbFolderPath)
    {
        if (!is_string($dbFolderPath)) {
            throw new NotStringException("dbFolderPath");
        }
        if ($dbFolderPath === "") {
            throw new EmptyStringException("dbFolderPath");
        }

        $this->dbFolderPath = rtrim($dbFolderPath, "/") . "/";
    }

    /**
     * @return string
     */
    public function getDbFolderPath(): string
    {
        return $this->dbFolderPath;
    }
}
