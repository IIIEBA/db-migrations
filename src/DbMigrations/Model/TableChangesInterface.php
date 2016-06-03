<?php

namespace DbMigrations\Model;

/**
 * Class TableChanges
 * @package DbMigrations\Model
 */
interface TableChangesInterface
{
    /**
     * @return string
     */
    public function getField();

    /**
     * @return TableChangesAction
     */
    public function getAction();
}
