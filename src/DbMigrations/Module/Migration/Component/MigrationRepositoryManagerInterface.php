<?php

declare(strict_types=1);

namespace DbMigrations\Module\Migration\Component;

use DbMigrations\Module\Migration\Dao\MigrationStatusRepositoryInterface;

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
     * @return MigrationStatusRepositoryInterface
     */
    public function get(string $dbName): MigrationStatusRepositoryInterface;
}
