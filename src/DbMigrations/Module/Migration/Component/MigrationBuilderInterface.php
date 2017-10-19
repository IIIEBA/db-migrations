<?php

declare(strict_types=1);

namespace DbMigrations\Module\Migration\Component;

use DbMigrations\Kernel\Exception\GeneralException;

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
     * @return MigrationInterface
     * @throws GeneralException
     */
    public function buildMigration(string $dbName, string $name): MigrationInterface;
}
