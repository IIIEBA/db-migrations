<?php

namespace DbMigrations\Model;

/**
 * Class InitDbResult
 * @package DbMigrations\Model
 */
interface InitDbResultInterface
{
    /**
     * Add table result
     *
     * @param InitTableResultInterface $result
     */
    public function addTableResult(InitTableResultInterface $result);

    /**
     * Get table result by name
     *
     * @param string $name
     * @return InitTableResultInterface|null
     */
    public function getTableResult($name);

    /**
     * Get result for all tables
     *
     * @return InitTableResultInterface[]
     */
    public function getResult();
}
