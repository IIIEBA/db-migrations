<?php

declare(strict_types=1);

namespace DbMigrations\Module\Schema\Model;

use DbMigrations\Module\Schema\Enum\TableRowType;

/**
 * Class TableRow
 * @package DbMigrations\Module\Schema\Model
 */
interface TableRowInterface
{
    /**
     * @return string|null
     */
    public function getName(): ?string;

    /**
     * @return string
     */
    public function getRow(): string;

    /**
     * @return TableRowType
     */
    public function getType(): TableRowType;
}
