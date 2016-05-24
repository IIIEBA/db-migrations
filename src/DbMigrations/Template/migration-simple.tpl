<?php

use BaseExceptions\Exception\LogicException\NotImplementedException;
use DbMigrations\Component\AbstractMigration;

/**
 * Simple migration
 *
 * Class %class-name%
 */
class %class-name% extends AbstractMigration
{
    /**
     * Run migration
     */
    public function up()
    {
        throw new NotImplementedException();
    }

    /**
     * Rollback migration
     */
    public function down()
    {
        throw new NotImplementedException();
    }
}
