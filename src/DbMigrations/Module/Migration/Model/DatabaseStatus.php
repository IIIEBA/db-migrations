<?php

declare(strict_types=1);

namespace DbMigrations\Module\Migration\Model;

use BaseExceptions\Exception\InvalidArgument\EmptyStringException;
use DbMigrations\Module\Migration\Enum\MigrationType;

/**
 * Class DatabaseStatus
 * @package DbMigrations\Module\Migration\Model
 */
class DatabaseStatus implements DatabaseStatusInterface
{
    /**
     * @var string
     */
    private $name;
    /**
     * @var array
     */
    private $migrationStatusList;
    /**
     * @var MigrationType
     */
    private $type;

    /**
     * DatabaseStatus constructor.
     *
     * @param string $name
     * @param MigrationType $type
     * @param array $migrationStatusList
     */
    public function __construct(
        string $name,
        MigrationType $type,
        array $migrationStatusList
    ) {
        if ($name === "") {
            throw new EmptyStringException("name");
        }

        array_walk(
            $migrationStatusList,
            function (MigrationStatusInterface $elm) {
                return $elm;
            }
        );

        $this->name = $name;
        $this->type = $type;
        $this->migrationStatusList = $migrationStatusList;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return MigrationType
     */
    public function getType(): MigrationType
    {
        return $this->type;
    }

    /**
     * @return MigrationStatusInterface[]
     */
    public function getMigrationStatusList(): array
    {
        return $this->migrationStatusList;
    }
}
