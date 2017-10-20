<?php

declare(strict_types=1);

namespace DbMigrations\Module\Migration\Enum;

use Enum\Lib\Enum;

/**
 * Class MigrationStatusType
 * @package DbMigrations\Module\Migration\Enum
 */
class MigrationStatusType extends Enum
{
    const APPLIED = "applied";
    const NEW = "new";
}
