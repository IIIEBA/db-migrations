<?php

declare(strict_types=1);

namespace DbMigrations\Module\Schema\Model;

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
     * TableRow constructor.
     *
     * @param string $row
     * @param TableRowType $type
     * @param string|null $name
     */
    public function __construct(
        string $row,
        TableRowType $type,
        string $name = null
    ) {
        $this->name = $name;
        $this->row = $row;
        $this->type = $type;
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
}
