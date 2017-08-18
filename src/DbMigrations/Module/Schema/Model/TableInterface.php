<?php

declare(strict_types=1);

namespace DbMigrations\Module\Schema\Model;

/**
 * Class Table
 * @package DbMigrations\Module\Schema\Model
 */
interface TableInterface
{
    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return string
     */
    public function getSchema(): string;

    /**
     * @return string
     */
    public function getSchemaPath(): string;
}
