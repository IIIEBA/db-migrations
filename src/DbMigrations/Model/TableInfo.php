<?php

namespace DbMigrations\Model;

use BaseExceptions\Exception\InvalidArgument\EmptyStringException;
use BaseExceptions\Exception\InvalidArgument\NotStringException;

/**
 * Class TableInfo
 * @package DbMigrations\Model
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
     * @var TableInfoStatus
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
     * @return TableInfoStatus
     */
    public function getStatus()
    {
        if (is_null($this->status)) {
            switch (true) {
                case is_null($this->schemaSyntax):
                    $this->status = new TableInfoStatus(TableInfoStatus::REMOVED);
                    break;
                
                case is_null($this->dbSyntax):
                    $this->status = new TableInfoStatus(TableInfoStatus::CREATED);
                    break;
                
                case count($this->changes):
                    $this->status = new TableInfoStatus(TableInfoStatus::MODIFIED);
                    break;

                default:
                    $this->status = new TableInfoStatus(TableInfoStatus::ACTUAL);
            }
        }

        return $this->status;
    }
}
