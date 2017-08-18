<?php

declare(strict_types=1);

namespace DbMigrations\Module\Schema\Model;

use BaseExceptions\Exception\InvalidArgument\EmptyStringException;
use DbMigrations\Module\Schema\Enum\TableChangesAction;

/**
 * Class TableChanges
 * @package bMigrations\Module\Schema\Model
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
        string $field,
        TableChangesAction $action
    ) {
        if ($field === "") {
            throw new EmptyStringException("field");
        }

        $this->field = trim(rtrim($field, ","));
        $this->action = $action;
    }

    /**
     * @return string
     */
    public function getField(): string
    {
        return $this->field;
    }

    /**
     * @return TableChangesAction
     */
    public function getAction(): TableChangesAction
    {
        return $this->action;
    }
}
