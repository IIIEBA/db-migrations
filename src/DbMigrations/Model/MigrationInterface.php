<?php

namespace DbMigrations\Model;

/**
 * Class Migration
 * @package DbMigrations\Model
 */
interface MigrationInterface
{
    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getClassName();

    /**
     * @return MigrationStatus
     */
    public function getStatus();
}
