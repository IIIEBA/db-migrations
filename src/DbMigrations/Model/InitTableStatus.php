<?php

namespace DbMigrations\Model;

use Enum\Lib\Enum;

/**
 * Class InitTableStatus
 * @package DbMigrations\Model
 */
class InitTableStatus extends Enum
{
    const CREATED = "created";
    const CREATED_WITHOUT_DATA = "created-without-data";
    const ALREADY_EXISTS = "already-exists";
    const ERROR = "error";
}
