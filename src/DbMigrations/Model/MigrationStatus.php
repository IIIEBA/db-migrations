<?php

namespace DbMigrations\Model;

use Enum\Lib\Enum;

/**
 * Class MigrationStatus
 * @package DbMigrations\Model
 */
class MigrationStatus extends Enum
{
    const NEW_ONE = "new";
    const APPLIED = "applied";
    const SKIPPED = "skipped";
}
