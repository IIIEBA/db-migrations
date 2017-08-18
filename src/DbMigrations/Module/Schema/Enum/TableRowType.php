<?php

declare(strict_types=1);

namespace DbMigrations\Module\Schema\Enum;

use Enum\Lib\Enum;

/**
 * Class TableRowType
 * @package DbMigrations\Module\Schema\Enum
 */
class TableRowType extends Enum
{
    const COLUMN = "column";
    const KEY = "key";
    const OTHER = "other";
}
