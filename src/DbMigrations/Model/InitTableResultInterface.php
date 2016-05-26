<?php

namespace DbMigrations\Model;

/**
 * Class InitTableResult
 * @package DbMigrations\Model
 */
interface InitTableResultInterface
{
    /**
     * @return string
     */
    public function getTableName();

    /**
     * @return InitTableStatus
     */
    public function getStatus();

    /**
     * @return null|string
     */
    public function getDesc();
}
