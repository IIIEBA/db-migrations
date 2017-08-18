<?php

declare(strict_types=1);

namespace DbMigrations\Module\Schema\Enum;

use Enum\Lib\Enum;

/**
 * Class TableChangesAction
 * @package bMigrations\Module\Schema\Enum
 */
class TableChangesAction extends Enum
{
    const ADD = "+";
    const REMOVE = "-";
    const MODIFIED = "?";
}
