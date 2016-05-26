<?php

namespace DbMigrations\Model;

use BaseExceptions\Exception\InvalidArgument\EmptyStringException;
use BaseExceptions\Exception\InvalidArgument\NotStringException;

/**
 * Class InitTableResult
 * @package DbMigrations\Model
 */
class InitTableResult implements InitTableResultInterface
{
    /**
     * @var string
     */
    private $tableName;
    /**
     * @var InitTableStatus
     */
    private $status;
    /**
     * @var null|string
     */
    private $desc;

    /**
     * InitTableResult constructor.
     * @param string $tableName
     * @param InitTableStatus $status
     * @param string|null $desc
     */
    public function __construct(
        $tableName,
        InitTableStatus $status,
        $desc = null
    ) {
        if (!is_string($tableName)) {
            throw new NotStringException("tableName");
        }
        if ($tableName === "") {
            throw new EmptyStringException("tableName");
        }
        
        if (!is_null($desc)) {
            if (!is_string($desc)) {
                throw new NotStringException("desc");
            }
            if ($desc === "") {
                throw new EmptyStringException("desc");
            }
        }

        $this->tableName = $tableName;
        $this->status = $status;
        $this->desc = $desc;
    }

    /**
     * @return string
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * @return InitTableStatus
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return null|string
     */
    public function getDesc()
    {
        return $this->desc;
    }
}
