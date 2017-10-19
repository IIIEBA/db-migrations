<?php

declare(strict_types=1);

namespace DbMigrations\Module\Migration\Component;

use DbMigrations\Kernel\Exception\GeneralException;

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
     * @param bool $isHeavyMigration
     * @return string
     * @throws GeneralException
     */
    public function generateMigration(string $dbName, string $name, bool $isHeavyMigration = false): string;
}
