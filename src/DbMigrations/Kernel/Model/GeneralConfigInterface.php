<?php

declare(strict_types=1);

namespace DbMigrations\Kernel\Model;

/**
 * Class GeneralConfig
 * @package Kernel\Model
 */
interface GeneralConfigInterface
{
    /**
     * @return string
     */
    public function getDbFolderPath(): string;
}
