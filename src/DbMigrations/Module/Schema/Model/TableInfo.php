<?php

declare(strict_types=1);

namespace DbMigrations\Module\Schema\Model;

use BaseExceptions\Exception\InvalidArgument\EmptyStringException;
use BaseExceptions\Exception\InvalidArgument\NotStringException;
use DbMigrations\Module\Schema\Enum\DbInfoStatus;

/**
 * Class TableInfo
 * @package bMigrations\Module\Schema\Model
 */
class TableInfo implements TableInfoInterface
{
    /**
     * @var string
     */
    private $tableName;
    /**
     * @var null|string
     */
    private $schemaSyntax;
    /**
     * @var null|string
     */
    private $dbSyntax;
    /**
     * @var TableChangesInterface[]
     */
    private $changes = [];
    /**
     * @var DbInfoStatus
     */
    private $status;

    /**
     * TableInfo constructor.
     *
     * @param string $tableName
     * @param string|null $schemaSyntax
     * @param string|null $dbSyntax
     */
    public function __construct(
        $tableName,
        $schemaSyntax = null,
        $dbSyntax = null
    ) {
        if (!is_string($tableName)) {
            throw new NotStringException("tableName");
        }
        if ($tableName === "") {
            throw new EmptyStringException("tableName");
        }
        
        if (!is_null($schemaSyntax)) {
            if (!is_string($schemaSyntax)) {
                throw new NotStringException("schemaSyntax");
            }
            if ($schemaSyntax === "") {
                throw new EmptyStringException("schemaSyntax");
            }
        }
        
        if (!is_null($dbSyntax)) {
            if (!is_string($dbSyntax)) {
                throw new NotStringException("dbSyntax");
            }
            if ($dbSyntax === "") {
                throw new EmptyStringException("dbSyntax");
            }
        }
        
        $this->tableName = $tableName;
        $this->schemaSyntax = $schemaSyntax;
        $this->dbSyntax = $dbSyntax;
    }

    /**
     * @return string
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * @return null|string
     */
    public function getSchemaSyntax()
    {
        return $this->schemaSyntax;
    }

    /**
     * @return null|string
     */
    public function getDbSyntax()
    {
        return $this->dbSyntax;
    }

    /**
     * Add changes to table info
     *
     * @param TableChangesInterface $changes
     */
    public function addChanges(TableChangesInterface $changes)
    {
        $this->changes[] = $changes;
    }

    /**
     * @return TableChangesInterface[]
     */
    public function getChanges()
    {
        return $this->changes;
    }

    /**
     * @return DbInfoStatus
     */
    public function getStatus()
    {
        if (is_null($this->status)) {
            switch (true) {
                case is_null($this->schemaSyntax):
                    $this->status = new DbInfoStatus(DbInfoStatus::REMOVED);
                    break;
                
                case is_null($this->dbSyntax):
                    $this->status = new DbInfoStatus(DbInfoStatus::CREATED);
                    break;
                
                case count($this->changes):
                    $this->status = new DbInfoStatus(DbInfoStatus::MODIFIED);
                    break;

                default:
                    $this->status = new DbInfoStatus(DbInfoStatus::ACTUAL);
            }
        }

        return $this->status;
    }
}
