<?php

declare(strict_types=1);

namespace DbMigrations\Module\Migration\Model;

use BaseExceptions\Exception\InvalidArgument\EmptyStringException;
use BaseExceptions\Exception\InvalidArgument\NotPositiveNumericException;
use DbMigrations\Module\Migration\Component\MigrationGenerator;
use DbMigrations\Module\Migration\Enum\MigrationStatusType;

/**
 * Class MigrationStatus
 * @package DbMigrations\Module\Migration\Model
 */
class MigrationStatus implements MigrationStatusInterface
{
    /**
     * @var string
     */
    private $migrationId;
    /**
     * @var string
     */
    private $name;
    /**
     * @var float|int|null
     */
    private $startedAt;
    /**
     * @var float|int|null
     */
    private $appliedAt;
    /**
     * @var int|null
     */
    private $id;
    /**
     * @var MigrationStatusType
     */
    private $type;
    /**
     * @var null|string
     */
    private $filename;

    /**
     * MigrationStatus constructor.
     *
     * @param string $migrationId
     * @param string $name
     * @param string|null $filename
     * @param MigrationStatusType|null $type
     * @param int|float|null $startedAt
     * @param int|float|null $appliedAt
     * @param int|null $id
     */
    public function __construct(
        string $migrationId,
        string $name,
        string $filename = null,
        MigrationStatusType $type = null,
        $startedAt = null,
        $appliedAt = null,
        int $id = null
    ) {
        if ($migrationId === "") {
            throw new EmptyStringException("migrationId");
        }

        if ($name === "") {
            throw new EmptyStringException("name");
        }

        if ($this->filename !== null) {
            if ($filename === "") {
                throw new EmptyStringException("filename");
            }
        } else {
            $filename = sprintf(MigrationGenerator::MIGRATION_CLASS_NAME_PATTERN, $migrationId, $name) . ".php";
        }

        if ($type === null) {
            $type = new MigrationStatusType(MigrationStatusType::NEW);
        }

        if ($startedAt === null) {
            $startedAt = microtime(true);
        }

        if ($appliedAt === null) {
            $appliedAt = microtime(true);
        }

        if ($id !== null && $id < 1) {
            throw new NotPositiveNumericException("id");
        }

        $this->migrationId = $migrationId;
        $this->name = $name;
        $this->filename = $filename;
        $this->startedAt = floatval($startedAt);
        $this->appliedAt = floatval($appliedAt);
        $this->id = $id;
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getMigrationId(): string
    {
        return $this->migrationId;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return MigrationStatusType
     */
    public function getType(): MigrationStatusType
    {
        return $this->type;
    }

    /**
     * @return null|string
     */
    public function getFilename():? string
    {
        return $this->filename;
    }

    /**
     * @return float
     */
    public function getStartedAt(): float
    {
        return $this->startedAt;
    }

    /**
     * @return float
     */
    public function getAppliedAt(): float
    {
        return $this->appliedAt;
    }

    /**
     * @return int|null
     */
    public function getId():? int
    {
        return $this->id;
    }
}
