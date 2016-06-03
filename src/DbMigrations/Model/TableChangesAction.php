<?php

namespace DbMigrations\Model;

use Enum\Lib\Enum;

/**
 * Class TableChangesAction
 * @package DbMigrations\Model
 */
class TableChangesAction extends Enum
{
    const ADD = "+";
    const REMOVE = "-";
}
