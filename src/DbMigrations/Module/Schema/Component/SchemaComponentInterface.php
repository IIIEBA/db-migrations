<?php

declare(strict_types=1);

namespace DbMigrations\Module\Schema\Component;

use DbMigrations\Kernel\Exception\GeneralException;

/**
 * Class SchemaComponent
 * @package Module\Schema\Command
 */
interface SchemaComponentInterface
{
    /**
     * Show db status
     *
     * @param string|null $database
     * @param string|null $tableName
     * @return void
     */
    public function showStatus(
        string $database = null,
        string $tableName = null
    ): void;

    /**
     * Init databases
     *
     * @param string|null $database
     * @param string|null $tableName
     * @param bool $withOutData
     */
    public function initDb(
        string $database = null,
        string $tableName = null,
        bool $withOutData = false
    ): void;

    /**
     * Dump database to schema files
     *
     * @param string|null $database
     * @param string|null $tableName
     * @throws GeneralException
     */
    public function dumpDb(
        string $database = null,
        string $tableName = null
    ): void;
}
