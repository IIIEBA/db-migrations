<?php

declare(strict_types=1);

namespace DbMigrations\Module\Migration\Component;

/**
 * Class MigrationInterface
 *
 * @package DbMigrations\Module\Migration\Component
 */
interface MigrationInterface
{
    /**
     * @return string
     */
    public function getId(): string;

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * Is heavy migration flag (for different apply and revert flow)
     *
     * @return bool
     */
    public function isHeavyMigration(): bool;
}
