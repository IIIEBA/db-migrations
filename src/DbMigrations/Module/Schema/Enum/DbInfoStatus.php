<?php

declare(strict_types=1);

namespace DbMigrations\Module\Schema\Enum;

use Enum\Lib\Enum;

/**
 * Class DbInfoStatus
 * @package bMigrations\Module\Schema\Enum
 */
class DbInfoStatus extends Enum
{
    const ACTUAL = "actual";
    const CREATED = "created";
    const REMOVED = "removed";
    const MODIFIED = "modified";
}
