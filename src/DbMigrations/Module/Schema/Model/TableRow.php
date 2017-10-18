<?php

declare(strict_types=1);

namespace DbMigrations\Module\Schema\Model;

use BaseExceptions\Exception\InvalidArgument\EmptyStringException;
use DbMigrations\Module\Schema\Enum\TableRowType;

/**
 * Class TableRow
 * @package DbMigrations\Module\Schema\Model
 */
class TableRow implements TableRowInterface
{
    /**
     * @var string|null
     */
    private $name;
    /**
     * @var string
     */
    private $row;
    /**
     * @var TableRowType
     */
    private $type;
    /**
     * @var null|string
     */
    private $location;

    /**
     * TableRow constructor.
     *
     * @param string $row
     * @param TableRowType $type
     * @param string|null $name
     * @param string|null $location
     */
    public function __construct(
        string $row,
        TableRowType $type,
        string $name = null,
        string $location = null
    ) {
        $this->name = $name;
        $this->row = $row;
        $this->type = $type;
        $this->location = $location;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getRow(): string
    {
        return $this->row;
    }

    /**
     * @return TableRowType
     */
    public function getType(): TableRowType
    {
        return $this->type;
    }

    /**
     * @return null|string
     */
    public function getLocation(): ?string
    {
        return $this->location;
    }

    /**
     * @param string $location
     * @return TableRowInterface
     */
    public function setLocation(string $location): TableRowInterface
    {
        if ($location === "") {
            throw new EmptyStringException("location");
        }

        $new = clone $this;
        $new->location = $location;

        return $new;
    }

    /**
     * @return string
     */
    public function getPreparedRow(): string
    {
        return $this->getRow() . ($this->getLocation() !== null ? (" " . $this->getLocation()) : "");
    }
}
