<?php

declare(strict_types=1);

namespace DbMigrations\Module\Schema\Model;

use BaseExceptions\Exception\InvalidArgument\EmptyStringException;
use BaseExceptions\Exception\InvalidArgument\NotObjectException;
use DbMigrations\Module\Schema\Enum\DbInfoStatus;

/**
 * Class DatabaseInfo
 * @package DbMigrations\Module\Schema\Model
 */
class DatabaseInfo implements DatabaseInfoInterface
{
    /**
     * @var string
     */
    private $name;
    /**
     * @var TableInfoInterface[]
     */
    private $tableList;
    /**
     * @var DbInfoStatus
     */
    private $status;

    /**
     * Database constructor.
     *
     * @param string $name
     * @param TableInfoInterface[] $tableList
     * @param DbInfoStatus $status
     */
    public function __construct(
        string $name,
        array $tableList,
        DbInfoStatus $status
    ) {
        if ($name === "") {
            throw new EmptyStringException("name");
        }

        array_walk($tableList, function ($elm) {
            if ($elm instanceof TableInfoInterface === false) {
                throw new NotObjectException("schemaList->elm", $elm);
            }
        });

        $this->name = $name;
        $this->tableList = $tableList;
        $this->status = $status;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return TableInfoInterface[]
     */
    public function getTableList(): array
    {
        return $this->tableList;
    }

    /**
     * @return DbInfoStatus
     */
    public function getStatus(): DbInfoStatus
    {
        return $this->status;
    }
}
