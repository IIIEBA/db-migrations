<?php

namespace DbMigrations\Model;

use BaseExceptions\Exception\InvalidArgument\EmptyStringException;
use BaseExceptions\Exception\InvalidArgument\NotArrayException;
use BaseExceptions\Exception\InvalidArgument\NotStringException;

/**
 * Class Config
 * @package DbMigrations\Model
 */
class Config
{
    /**
     * @var array
     */
    private $data;
    /**
     * @var mixed|null
     */
    private $mysqlHost;
    /**
     * @var mixed|null
     */
    private $mysqlPort;
    /**
     * @var mixed|null
     */
    private $mysqlUsername;
    /**
     * @var mixed|null
     */
    private $mysqlPassword;
    /**
     * @var mixed|null
     */
    private $mysqlDatabase;
    /**
     * @var mixed|null
     */
    private $mysqlCharset;
    /**
     * @var mixed|null
     */
    private $generalSchemaPath;

    /**
     * Config constructor.
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;

        // Mysql configs
        $this->mysqlHost = $this->get("host", "mysql", "127.0.0.1");
        $this->mysqlPort = $this->get("port", "mysql", 3306);
        $this->mysqlUsername = $this->get("username", "mysql");
        $this->mysqlPassword = $this->get("password", "mysql");
        $this->mysqlDatabase = $this->get("database", "mysql");
        $this->mysqlCharset = $this->get("charset", "mysql", "utf8");

        // General configs
        $this->generalSchemaPath = $this->get("schemaPath", "general", "schema");
    }

    /**
     * Get selected param
     *
     * @param string $name
     * @param string|null $block
     * @param mixed|null $default
     * @return mixed|null
     */
    public function get($name, $block = null, $default = null) {
        if (!is_string($name)) {
            throw new NotStringException("name");
        }
        if ($name === "") {
            throw new EmptyStringException("name");
        }

        if (!is_null($block)) {
            if (!is_string($block)) {
                throw new NotStringException("block");
            }
            if ($block === "") {
                throw new EmptyStringException("block");
            }
            if (array_key_exists($block, $this->data) === false || !is_array($this->data[$block])) {
                throw new NotArrayException("$block in data");
            }

            $data = $this->data[$block];
        } else {
            $data = $this->data;
        }

        if (!is_null($default)) {
            if (!is_scalar($default)) {
                throw new \InvalidArgumentException("Only scalar allowed for default");
            }
        }

        return array_key_exists($name, $data) ? $data[$name] : $default;
    }

    /**
     * @return mixed|null
     */
    public function getMysqlHost()
    {
        return $this->mysqlHost;
    }

    /**
     * @return mixed|null
     */
    public function getMysqlPort()
    {
        return $this->mysqlPort;
    }

    /**
     * @return mixed|null
     */
    public function getMysqlUsername()
    {
        return $this->mysqlUsername;
    }

    /**
     * @return mixed|null
     */
    public function getMysqlPassword()
    {
        return $this->mysqlPassword;
    }

    /**
     * @return mixed|null
     */
    public function getMysqlDatabase()
    {
        return $this->mysqlDatabase;
    }

    /**
     * @return mixed|null
     */
    public function getMysqlCharset()
    {
        return $this->mysqlCharset;
    }

    /**
     * @return mixed|null
     */
    public function getGeneralSchemaPath()
    {
        return $this->generalSchemaPath;
    }
}
