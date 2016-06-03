<?php

namespace DbMigrations\Model;

use BaseExceptions\Exception\InvalidArgument\EmptyStringException;
use BaseExceptions\Exception\InvalidArgument\NotStringException;

/**
 * Class TableChanges
 * @package DbMigrations\Model
 */
class TableChanges implements TableChangesInterface
{
    /**
     * @var string
     */
    private $field;
    /**
     * @var TableChangesAction
     */
    private $action;

    /**
     * TableChanges constructor.
     * @param string $field
     * @param TableChangesAction $action
     */
    public function __construct(
        $field,
        TableChangesAction $action
    ) {
        if (!is_string($field)) {
            throw new NotStringException("field");
        }
        if ($field === "") {
            throw new EmptyStringException("field");
        }
        
        $this->field = trim(rtrim($field, ","));
        $this->action = $action;
    }

    /**
     * @return string
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * @return TableChangesAction
     */
    public function getAction()
    {
        return $this->action;
    }
}
