<?php

namespace DbMigrations\Component;

/**
 * Class MigrationInterface
 * @package DbMigrations\Component
 */
interface MigrationInterface
{
    /**
     * Run migration
     */
    public function up();

    /**
     * Rollback migration
     */
    public function down();

    /**
     * Run migration in transaction
     */
    public function safeUp();

    /**
     * Rollback migration in transaction
     */
    public function safeDown();
}
