<?php

declare(strict_types=1);

namespace DbMigrations\Module\Migration\Dao;

use DbMigrations\Kernel\Exception\GeneralException;
use DbMigrations\Module\Migration\Model\MigrationStatusInterface;

/**
 * Class MigrationStatusRepository
 * @package DbMigrations\Module\Migration\Dao
 */
interface MigrationStatusRepositoryInterface
{
    /**
     * Find migrations from table
     *
     * @param string|null $migrationId
     * @return MigrationStatusInterface[]
     */
    public function findMigrations(string $migrationId = null): array;

    /**
     * @param MigrationStatusInterface $object
     * @return MigrationStatusInterface
     * @throws GeneralException
     */
    public function store(MigrationStatusInterface $object): MigrationStatusInterface;

    /**
     * Check is we get connection with selected database
     *
     * @throws GeneralException
     */
    public function checkDatabase(): void;
}
