<?php

namespace DbMigrations\Model;

/**
 * Class TableInfo
 * @package DbMigrations\Model
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
     * @return TableInfoStatus
     */
    public function getStatus();
}
