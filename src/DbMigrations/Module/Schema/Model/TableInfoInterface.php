<?php

declare(strict_types=1);

namespace DbMigrations\Module\Schema\Model;

use DbMigrations\Module\Schema\Enum\DbInfoStatus;

/**
 * Class TableInfo
 * @package bMigrations\Module\Schema\Model
 */
interface TableInfoInterface
{
    /**
     * @return string
     */
    public function getTableName();

    /**
     * @return null|string
     */
    public function getSchemaSyntax();

    /**
     * @return null|string
     */
    public function getDbSyntax();

    /**
     * Add changes to table info
     *
     * @param TableChangesInterface $changes
     */
    public function addChanges(TableChangesInterface $changes);

    /**
     * @return TableChangesInterface[]
     */
    public function getChanges();

    /**
     * @return DbInfoStatus
     */
    public function getStatus();
}
