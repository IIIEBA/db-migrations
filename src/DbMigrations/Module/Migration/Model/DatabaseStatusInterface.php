<?php

declare(strict_types=1);

namespace DbMigrations\Module\Migration\Model;

use DbMigrations\Module\Migration\Enum\MigrationType;

/**
 * Class DatabaseStatus
 * @package DbMigrations\Module\Migration\Model
 */
interface DatabaseStatusInterface
{
    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return MigrationType
     */
    public function getType(): MigrationType;

    /**
     * @return MigrationStatusInterface[]
     */
    public function getMigrationStatusList(): array;
}
