<?php

declare(strict_types=1);

namespace DbMigrations\Module\Migration\Component;

use DbMigrations\Module\Migration\Enum\MigrationType;

/**
 * Class MigrationComponent
 * @package DbMigrations\Module\MIgration\Component
 */
interface MigrationComponentInterface
{
    /**
     * Create new migration for selected database
     *
     * @param string $dbName
     * @param string $name
     * @param MigrationType $type
     * @param bool $isHeavyMigration
     * @param string|null $schemaName
     */
    public function createMigration(
        string $dbName,
        string $name,
        MigrationType $type,
        bool $isHeavyMigration = false,
        string $schemaName = null
    ): void;

    /**
     * Migrate to selected migration or only selected migration
     *
     * @param MigrationType $type
     * @param string|null $dbName
     * @param string|null $migrationId
     * @param bool $onlySingle
     */
    public function migrationsUp(
        MigrationType $type,
        string $dbName = null,
        string $migrationId = null,
        bool $onlySingle = false
    ): void;

    /**
     * Rollback to selected migration or only selected migration
     *
     * @param string $dbName
     * @param string $migrationId
     * @param MigrationType $type
     * @param bool $onlySingle
     */
    public function migrationsDown(
        string $dbName,
        string $migrationId,
        MigrationType $type,
        bool $onlySingle = false
    ): void;

    /**
     * Show migrations status
     *
     * @param MigrationType $type
     * @param string|null $dbName
     * @param string|null $migrationId
     */
    public function migrationsStatus(
        MigrationType $type,
        string $dbName = null,
        string $migrationId = null
    ): void;
}
