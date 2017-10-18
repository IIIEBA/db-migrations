<?php

declare(strict_types=1);

namespace DbMigrations\Kernel\Component;

use DbMigrations\Kernel\Exception\GeneralException;

/**
 * Class DbConnection
 * @package Kernel\Component
 */
interface DbConnectionInterface
{
    /**
     * @param string $name
     * @return \PDO
     */
    public function getConnection($name): \PDO;

    /**
     * @return \PDO
     * @throws GeneralException
     */
    public function getDefaultConnection(): \PDO;

    /**
     * Get list of configured connection names
     *
     * @return string[]
     */
    public function getConnectionNamesList(): array;

    /**
     * @param string $name
     * @param \PDO $connectionList
     * @throws GeneralException
     */
    public function setConnection($name, \PDO $connectionList);
}
