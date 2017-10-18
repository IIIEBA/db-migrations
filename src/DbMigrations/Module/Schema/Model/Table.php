<?php

declare(strict_types=1);

namespace DbMigrations\Module\Schema\Model;

use BaseExceptions\Exception\InvalidArgument\EmptyStringException;

/**
 * Class Table
 * @package DbMigrations\Module\Schema\Model
 */
class Table implements TableInterface
{
    /**
     * @var string
     */
    private $name;
    /**
     * @var string
     */
    private $schema;
    /**
     * @var string
     */
    private $schemaPath;

    /**
     * Table constructor.
     *
     * @param string $name
     * @param string $schema
     * @param string $schemaPath
     */
    public function __construct(
        string $name,
        string $schema,
        string $schemaPath
    ) {
        if ($name === "") {
            throw new EmptyStringException("name");
        }

        if ($schema === "") {
            throw new EmptyStringException("schema");
        }

        if ($schemaPath === "") {
            throw new EmptyStringException("schemaPath");
        }

        $this->name = $name;
        $this->schema = $schema;
        $this->schemaPath = $schemaPath;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getSchema(): string
    {
        return $this->schema;
    }

    /**
     * @return string
     */
    public function getSchemaPath(): string
    {
        return $this->schemaPath;
    }
}
