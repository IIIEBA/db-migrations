<?php

declare(strict_types=1);

namespace DbMigrations\Module\Migration\Enum;

use Enum\Lib\Enum;

/**
 * Class MigrationType
 * @package DbMigrations\Module\Migration\Enum
 */
class MigrationType extends Enum
{
    const STRUCTURE = "structure";
    const DATA = "data";
}
