<?php

declare(strict_types=1);

namespace DbMigrations\Module\Migration\Model;

use DbMigrations\Module\Migration\Enum\MigrationStatusType;

/**
 * Class MigrationStatus
 * @package DbMigrations\Module\Migration\Model
 */
interface MigrationStatusInterface
{
    /**
     * @return string
     */
    public function getMigrationId(): string;

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return MigrationStatusType
     */
    public function getType(): MigrationStatusType;

    /**
     * @return null|string
     */
    public function getFilename():? string;

    /**
     * @return float
     */
    public function getStartedAt(): float;

    /**
     * @return float
     */
    public function getAppliedAt(): float;

    /**
     * @return int|null
     */
    public function getId():? int;
}
