<?php

declare(strict_types=1);

namespace DbMigrations\Module\Migration\Component;

/**
 * Class MigrationInterface
 * @package DbMigrations\Module\Migration\Component
 */
interface MigrationInterface
{
    /**
     * Apply migration
     */
    public function up(): void;

    /**
     * Revert migration
     */
    public function down(): void;

    /**
     * @return string
     */
    public function getId(): string;

    /**
     * @return string
     */
    public function getName(): string;
}
