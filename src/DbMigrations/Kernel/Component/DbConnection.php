<?php

declare(strict_types=1);

namespace DbMigrations\Kernel\Component;

use BaseExceptions\Exception\InvalidArgument\EmptyStringException;
use BaseExceptions\Exception\InvalidArgument\NotStringException;
use DbMigrations\Kernel\Exception\GeneralException;
use DbMigrations\Kernel\Util\LoggerTrait;
use Psr\Log\LoggerInterface;

/**
 * Class DbConnection
 * @package Kernel\Component
 */
class DbConnection implements DbConnectionInterface
{
    use LoggerTrait;

    const DEFAULT_CONNECTION_NAME = "_default";

    /**
     * @var \PDO[]
     */
    private $connectionList = [];

    /**
     * DbConnection constructor.
     *
     * @param LoggerInterface|null $logger
     */
    public function __construct(LoggerInterface $logger = null)
    {
        $this->setLogger($logger);
    }

    /**
     * @param string $name
     * @return \PDO
     */
    public function getConnection($name): \PDO
    {
        if (!is_string($name)) {
            throw new NotStringException("name");
        }
        if ($name === "") {
            throw new EmptyStringException("name");
        }

        $connection = array_key_exists($name, $this->connectionList)
            ? $this->connectionList[$name] : $this->getDefaultConnection();

        if ($this->isDatabaseExists($name, $connection)) {
            $connection->exec("USE `{$name}`");
        }

        return $connection;
    }

    /**
     * @return \PDO
     * @throws GeneralException
     */
    public function getDefaultConnection(): \PDO
    {
        if (!array_key_exists(self::DEFAULT_CONNECTION_NAME, $this->connectionList)) {
            throw new GeneralException("Default db connection is not configured");
        }

        return $this->connectionList[self::DEFAULT_CONNECTION_NAME];
    }

    /**
     * Get list of configured connection names
     *
     * @return string[]
     */
    public function getConnectionNamesList(): array
    {
        $list = $this->connectionList;
        unset($list[self::DEFAULT_CONNECTION_NAME]);

        return array_keys($list);
    }

    /**
     * @param string $name
     * @param \PDO $connectionList
     * @throws GeneralException
     */
    public function setConnection($name, \PDO $connectionList)
    {
        if (!is_string($name)) {
            throw new NotStringException("name");
        }
        if ($name === "") {
            throw new EmptyStringException("name");
        }
        if (array_key_exists($name, $this->connectionList)) {
            throw new GeneralException("Connection with same name is already exists - `{$name}`");
        }

        $this->connectionList[$name] = $connectionList;
    }

    /**
     * Check is database exists
     *
     * @param string $database
     * @param \PDO $connection
     * @return bool
     */
    public function isDatabaseExists(string $database, \PDO $connection): bool
    {
        if ($database === "") {
            throw new EmptyStringException("database");
        }

        $sql = "SHOW DATABASES LIKE '{$database}'";
        $result = $connection->query($sql)->rowCount();

        return boolval($result);
    }
}
