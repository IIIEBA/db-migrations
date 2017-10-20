<?php

declare(strict_types=1);

namespace DbMigrations\Module\Migration\Dao;

use BaseExceptions\Exception\InvalidArgument\NotPositiveNumericException;
use DbMigrations\Kernel\Exception\GeneralException;
use DbMigrations\Kernel\Util\LoggerTrait;
use DbMigrations\Module\Migration\Enum\MigrationStatusType;
use DbMigrations\Module\Migration\Map\MigrationStatusMapper;
use DbMigrations\Module\Migration\Model\MigrationStatusInterface;
use PDO;
use PDOStatement;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class MigrationStatusRepository
 * @package DbMigrations\Module\Migration\Dao
 */
class MigrationStatusRepository implements MigrationStatusRepositoryInterface
{
    use LoggerTrait;

    const MIGRATION_STATUS_TABLE_NAME_REGEXP = "/%tableName%/";

    /**
     * @var PDO
     */
    private $connection;
    /**
     * @var string
     */
    private $tableName;
    /**
     * @var Filesystem
     */
    private $filesystem;
    /**
     * @var string
     */
    private $dbTableTemplatePath;
    /**
     * @var bool
     */
    private $isDbChecked = false;
    /**
     * @var bool
     */
    private $isTableChecked = false;
    /**
     * @var MigrationStatusMapper
     */
    private $mapper;

    /**
     * MigrationStatus constructor.
     *
     * @param PDO $connection
     * @param Filesystem $filesystem
     * @param string $type
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        PDO $connection,
        Filesystem $filesystem,
        string $type,
        LoggerInterface $logger = null
    ) {
        $this->setLogger($logger);

        $this->connection = $connection;
        $this->filesystem = $filesystem;
        $this->tableName = "_db_" . $type;
        $this->dbTableTemplatePath = __DIR__ . "/../Template/dbMigrationStatusTable.txt";

        $this->mapper = new MigrationStatusMapper();
    }

    /**
     * Execute action with db and table check
     *
     * @param string $sql
     * @param array $data
     * @return PDOStatement
     */
    public function exec(string $sql, array $data = []): PDOStatement
    {
        $this->checkDatabase();
        $this->checkAndCreateStatusTableIfNotExist();

        $statement = $this->connection->prepare($sql);
        $statement->execute($data);

        return $statement;
    }

    /**
     * Find migration by autoincrement id
     *
     * @param int $id
     * @return MigrationStatusInterface|null
     */
    public function findById(int $id):? MigrationStatusInterface
    {
        if ($id < 1) {
            throw new NotPositiveNumericException("id");
        }

        $rawData = $this->exec(
            "SELECT * FROM `{$this->tableName}` WHERE `id` = :id LIMIT 1",
            ["id" => $id]
        )->fetchAll(
            PDO::FETCH_ASSOC
        );

        return count($rawData) === 1 ? reset($rawData) : null;
    }

    /**
     * Find migrations from table
     *
     * @param string|null $migrationId
     * @return MigrationStatusInterface[]
     */
    public function findMigrations(string $migrationId = null): array
    {
        $result = [];
        $where = "";
        $data = [];

        if ($migrationId) {
            $where .= "WHERE `migrationId` = :migrationId";
            $data["migrationId"] = $migrationId;
        }

        $rawData = $this->exec(
            "SELECT * FROM `{$this->tableName}` {$where} ORDER BY `id` DESC",
            $data
        )->fetchAll(
            PDO::FETCH_ASSOC
        );

        foreach ($rawData as $data) {
            $data["type"] = MigrationStatusType::APPLIED;
            $result[] = $this->mapper->convertToObject($data);
        }

        return $result;
    }

    /**
     * @param MigrationStatusInterface $object
     * @return MigrationStatusInterface
     * @throws GeneralException
     */
    public function store(MigrationStatusInterface $object): MigrationStatusInterface
    {
        if ($object->getId() !== null && $this->findById($object->getId()) !== null) {
            // Update existed row
            throw new GeneralException("It is not supported to update status rows, only create new one");
        } else {
            // Create new row
            $this->exec(
                "INSERT INTO `{$this->tableName}`"
                    . "VALUES('migrationId', 'name', 'startedAt', 'appliedAt')"
                    . "(:migrationId, :name, :startedAt, :appliedAt)",
                [
                    "migrationId" => $object->getMigrationId(),
                    "name" => $object->getName(),
                    "startedAt" => $object->getStartedAt(),
                    "appliedAt" => $object->getAppliedAt(),
                ]
            );

            $row = $this->findById(intval($this->connection->lastInsertId()));
            if ($row === null) {
                throw new GeneralException("Failed to insert new migration status row to table {$this->tableName}");
            }
        }

        return $row;
    }

    /**
     * Check is we get connection with selected database
     *
     * @throws GeneralException
     */
    public function checkDatabase()
    {
        if ($this->isDbChecked === false) {
            $dbName = $this->exec("SELECT DATABASE() AS db")->fetchColumn();
            if ($dbName === null) {
                throw new GeneralException("Migration can be ran only for existed database, try to init it first");
            }

            $this->isDbChecked = true;
        }
    }

    /**
     * Check is migration status table exist and create it if not
     *
     * @throws GeneralException
     */
    public function checkAndCreateStatusTableIfNotExist()
    {
        if ($this->isTableChecked === false) {
            if ($this->exec("SHOW TABLES LIKE '{$this->tableName}'")->rowCount() === 0) {
                if ($this->filesystem->exists($this->dbTableTemplatePath) == false) {
                    throw new GeneralException(
                        "Migration status table template doesn`t exist by path [{$this->dbTableTemplatePath}]"
                    );
                }

                $sql = preg_replace(
                    self::MIGRATION_STATUS_TABLE_NAME_REGEXP,
                    $this->tableName,
                    file_get_contents($this->dbTableTemplatePath)
                );

                $this->connection->exec($sql);
            }

            $this->isTableChecked = true;
        }
    }
}
