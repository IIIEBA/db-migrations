<?php

declare(strict_types=1);

namespace DbMigrations\Module\Schema\Model;

use DbMigrations\Module\Schema\Enum\TableChangesAction;

/**
 * Class TableChanges
 * @package bMigrations\Module\Schema\Model
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
