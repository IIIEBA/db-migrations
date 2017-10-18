<?php

declare(strict_types=1);

namespace DbMigrations\Module\Schema\Model;

use DbMigrations\Module\Schema\Enum\DbInfoStatus;

/**
 * Class DatabaseInfo
 * @package DbMigrations\Module\Schema\Model
 */
interface DatabaseInfoInterface
{
    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return TableInfoInterface[]
     */
    public function getTableList(): array;

    /**
     * @return DbInfoStatus
     */
    public function getStatus(): DbInfoStatus;
}
