<?php

declare(strict_types=1);

namespace DbMigrations\Module\Migration\Component;

use BaseExceptions\Exception\LogicException\NotImplementedException;
use DbMigrations\Kernel\Component\DbConnectionInterface;
use DbMigrations\Kernel\Model\GeneralConfigInterface;
use DbMigrations\Kernel\Util\LoggerTrait;
use DbMigrations\Kernel\Util\StdInHelper;
use DbMigrations\Module\Migration\Enum\MigrationType;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class MigrationComponent
 * @package DbMigrations\Module\MIgration\Component
 */
class MigrationComponent implements MigrationComponentInterface
{
    use LoggerTrait;

    const DEFAULT_FOLDER_PERMISSIONS = 0755;

    /**
     * @var GeneralConfigInterface
     */
    private $config;
    /**
     * @var DbConnectionInterface
     */
    private $dbConnection;
    /**
     * @var Filesystem
     */
    private $filesystem;
    /**
     * @var OutputInterface
     */
    private $output;
    /**
     * @var StdInHelper
     */
    private $stdInHelper;
    /**
     * @var string
     */
    private $migrationFolderPath;
    /**
     * @var string
     */
    private $dataFolderPath;
    /**
     * @var MigrationBuilderInterface
     */
    private $structureMigrationBuilder;
    /**
     * @var MigrationBuilderInterface
     */
    private $dataMigrationBuilder;
    /**
     * @var MigrationGeneratorInterface
     */
    private $structureMigrationGenerator;
    /**
     * @var MigrationGeneratorInterface
     */
    private $dataMigrationGenerator;

    /**
     * MigrationComponent constructor.
     *
     * @param GeneralConfigInterface $config
     * @param DbConnectionInterface $dbConnection
     * @param Filesystem $filesystem
     * @param OutputInterface $output
     * @param StdInHelper $stdInHelper
     * @param MigrationBuilderInterface $structureMigrationBuilder
     * @param MigrationBuilderInterface $dataMigrationBuilder
     * @param MigrationGeneratorInterface $structureMigrationGenerator
     * @param MigrationGeneratorInterface $dataMigrationGenerator
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        GeneralConfigInterface $config,
        DbConnectionInterface $dbConnection,
        Filesystem $filesystem,
        OutputInterface $output,
        StdInHelper $stdInHelper,
        MigrationBuilderInterface $structureMigrationBuilder,
        MigrationBuilderInterface $dataMigrationBuilder,
        MigrationGeneratorInterface $structureMigrationGenerator,
        MigrationGeneratorInterface $dataMigrationGenerator,
        LoggerInterface $logger = null
    ) {
        $this->setLogger($logger);

        $this->config = $config;
        $this->dbConnection = $dbConnection;
        $this->filesystem = $filesystem;
        $this->output = $output;
        $this->stdInHelper = $stdInHelper;
        $this->structureMigrationBuilder = $structureMigrationBuilder;
        $this->dataMigrationBuilder = $dataMigrationBuilder;
        $this->structureMigrationGenerator = $structureMigrationGenerator;
        $this->dataMigrationGenerator = $dataMigrationGenerator;

        $this->migrationFolderPath = PROJECT_ROOT . $config->getDbFolderPath() . MigrationType::STRUCTURE . "/";
        $this->dataFolderPath = PROJECT_ROOT . $config->getDbFolderPath() . MigrationType::DATA . "/";
    }

    /**
     * Create new migration for selected database
     *
     * @param string $dbName
     * @param string $name
     * @param MigrationType $type
     * @param bool $isHeavyMigration
     * @param string|null $schemaName
     */
    public function createMigration(
        string $dbName,
        string $name,
        MigrationType $type,
        bool $isHeavyMigration = false,
        string $schemaName = null
    ): void {
        throw new NotImplementedException();
    }

    /**
     * Migrate to selected migration or only selected migration
     *
     * @param MigrationType $type
     * @param string|null $dbName
     * @param string|null $migrationId
     * @param bool $onlySingle
     */
    public function migrationsUp(
        MigrationType $type,
        string $dbName = null,
        string $migrationId = null,
        bool $onlySingle = false
    ): void {
        throw new NotImplementedException();
    }

    /**
     * Rollback to selected migration or only selected migration
     *
     * @param string $dbName
     * @param string $migrationId
     * @param MigrationType $type
     * @param bool $onlySingle
     */
    public function migrationsDown(
        string $dbName,
        string $migrationId,
        MigrationType $type,
        bool $onlySingle = false
    ): void {
        throw new NotImplementedException();
    }

    /**
     * Show migrations status
     *
     * @param MigrationType $type
     * @param string|null $dbName
     * @param string|null $migrationId
     */
    public function migrationsStatus(
        MigrationType $type,
        string $dbName = null,
        string $migrationId = null
    ): void {
        throw new NotImplementedException();
    }
}
