<?php

namespace DbMigrations\Component;

/**
 * Class MigrationCommandInterface
 * @package DbMigrations\Component
 */
interface MigrationCommandInterface
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
