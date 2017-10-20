<?php

declare(strict_types=1);

namespace DbMigrations\Module\Migration\Component;

use DbMigrations\Module\Migration\Dao\MigrationStatusRepositoryInterface;
use DbMigrations\Module\Migration\Enum\MigrationType;

/**
 * Class MigrationRepositoryManager
 * @package DbMigrations\Module\Migration\Component
 */
interface MigrationRepositoryManagerInterface
{
    /**
     * Get existed or generate new migration repository for each variant of params
     *
     * @param string $dbName
     * @param MigrationType $type
     * @return MigrationStatusRepositoryInterface
     */
    public function get(string $dbName, MigrationType $type): MigrationStatusRepositoryInterface;
}
