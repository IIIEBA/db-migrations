<?php

declare(strict_types=1);

namespace DbMigrations\Module\Schema\Model;

use BaseExceptions\Exception\InvalidArgument\EmptyStringException;
use BaseExceptions\Exception\InvalidArgument\NotObjectException;

/**
 * Class Database
 * @package Module\Schema\Model
 */
class Database implements DatabaseInterface
{
    /**
     * @var string
     */
    private $name;
    /**
     * @var array
     */
    private $schemaList;

    /**
     * Database constructor.
     * @param string $name
     * @param TableInterface[] $schemaList
     */
    public function __construct(
        string $name,
        array $schemaList
    ) {
        if ($name === "") {
            throw new EmptyStringException("name");
        }

        array_walk($schemaList, function ($elm) {
            if ($elm instanceof TableInterface === false) {
                throw new NotObjectException("schemaList->elm", $elm);
            }
        });

        $this->name = $name;
        $this->schemaList = $schemaList;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return TableInterface[]
     */
    public function getSchemaList(): array
    {
        return $this->schemaList;
    }

}
