<?php

declare(strict_types=1);

namespace DbMigrations\Module\Schema\Model;

/**
 * Class Database
 * @package Module\Schema\Model
 */
interface DatabaseInterface
{
    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return TableInterface[]
     */
    public function getSchemaList(): array;
}
