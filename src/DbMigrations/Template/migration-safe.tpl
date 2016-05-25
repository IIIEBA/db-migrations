<?php

use BaseExceptions\Exception\LogicException\NotImplementedException;
use DbMigrations\Component\AbstractMigration;

/**
 * Migration which will run in transaction
 *
 * Class %class-name%
 */
class %class-name% extends AbstractMigration
{
    /**
     * Run migration in transaction
     */
    public function safeUp()
    {
        throw new NotImplementedException();
    }

    /**
     * Rollback migration in transaction
     */
    public function safeDown()
    {
        throw new NotImplementedException();
    }
}