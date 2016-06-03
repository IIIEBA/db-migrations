<?php

namespace DbMigrations\Model;

use Enum\Lib\Enum;

/**
 * Class TableInfoStatus
 * @package DbMigrations\Model
 */
class TableInfoStatus extends Enum
{
    const ACTUAL = "actual";
    const CREATED = "created";
    const REMOVED = "removed";
    const MODIFIED = "modified";
}
