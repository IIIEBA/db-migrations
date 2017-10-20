<?php

declare(strict_types=1);

namespace DbMigrations\Module\Migration\Component;

use DbMigrations\Module\Migration\Enum\MigrationType;

/**
 * Class MigrationBuilder
 * @package DbMigrations\Module\Migration\Component
 */
interface MigrationBuilderInterface
{
    /**
     * Build db migration class by name
     *
     * @param string $dbName
     * @param string $name
     * @param MigrationType $type
     * @return MigrationInterface
     */
    public function buildMigration(
        string $dbName,
        string $name,
        MigrationType $type
    ): MigrationInterface;
}
