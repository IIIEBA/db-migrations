<?php

declare(strict_types=1);

namespace DbMigrations\Module\Migration\Component;

use DbMigrations\Module\Migration\Enum\MigrationType;

/**
 * Class MigrationGenerator
 * @package DbMigrations\Module\Migration\Component
 */
interface MigrationGeneratorInterface
{
    /**
     * Generate new migration
     *
     * @param string $dbName
     * @param string $name
     * @param MigrationType $type
     * @param bool $isHeavyMigration
     * @return string
     */
    public function generateMigration(
        string $dbName,
        string $name,
        MigrationType $type,
        bool $isHeavyMigration = false
    ): string;
}
