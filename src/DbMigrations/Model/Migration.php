<?php

namespace DbMigrations\Model;

use BaseExceptions\Exception\InvalidArgument\EmptyStringException;
use BaseExceptions\Exception\InvalidArgument\NotStringException;

/**
 * Class Migration
 * @package DbMigrations\Model
 */
class Migration implements MigrationInterface
{
    /**
     * @var string
     */
    private $name;
    /**
     * @var MigrationStatus
     */
    private $status;
    /**
     * @var string
     */
    private $className;

    /**
     * Migration constructor.
     * @param string $name
     * @param string $className
     * @param MigrationStatus $status
     */
    public function __construct(
        $name,
        $className,
        MigrationStatus $status
    ) {
        if (!is_string($name)) {
            throw new NotStringException("name");
        }
        if ($name === "") {
            throw new EmptyStringException("name");
        }
        
        if (!is_string($className)) {
            throw new NotStringException("className");
        }
        if ($className === "") {
            throw new EmptyStringException("className");
        }

        $this->name = $name;
        $this->status = $status;
        $this->className = $className;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * @return MigrationStatus
     */
    public function getStatus()
    {
        return $this->status;
    }
}
