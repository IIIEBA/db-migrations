<?php

declare(strict_types=1);

namespace DbMigrations\Module\Migration\Component;

use BaseExceptions\Exception\InvalidArgument\EmptyStringException;
use DbMigrations\Kernel\Component\DbConnectionInterface;
use DbMigrations\Kernel\Model\GeneralConfigInterface;
use DbMigrations\Kernel\Util\LoggerTrait;
use DbMigrations\Kernel\Util\StdInHelper;
use DbMigrations\Module\Migration\Enum\MigrationStatusType;
use DbMigrations\Module\Migration\Enum\MigrationType;
use DbMigrations\Module\Migration\Model\DatabaseStatus;
use DbMigrations\Module\Migration\Model\DatabaseStatusInterface;
use DbMigrations\Module\Migration\Model\MigrationStatus;
use DbMigrations\Module\Migration\Model\MigrationStatusInterface;
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
    const CLASS_NAME_REGEXP = "/Migration_(\d+)_([a-zA-Z0-9]+)(\.php)*$/";

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
     * @var MigrationRepositoryManagerInterface
     */
    private $migrationRepositoryManager;
    /**
     * @var MigrationBuilderInterface
     */
    private $migrationBuilder;
    /**
     * @var MigrationGeneratorInterface
     */
    private $migrationGenerator;

    /**
     * MigrationComponent constructor.
     *
     * @param GeneralConfigInterface $config
     * @param DbConnectionInterface $dbConnection
     * @param Filesystem $filesystem
     * @param OutputInterface $output
     * @param StdInHelper $stdInHelper
     * @param MigrationBuilderInterface $migrationBuilder
     * @param MigrationGeneratorInterface $migrationGenerator
     * @param MigrationRepositoryManagerInterface $migrationRepositoryManager
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        GeneralConfigInterface $config,
        DbConnectionInterface $dbConnection,
        Filesystem $filesystem,
        OutputInterface $output,
        StdInHelper $stdInHelper,
        MigrationBuilderInterface $migrationBuilder,
        MigrationGeneratorInterface $migrationGenerator,
        MigrationRepositoryManagerInterface $migrationRepositoryManager,
        LoggerInterface $logger = null
    ) {
        $this->setLogger($logger);

        $this->config = $config;
        $this->dbConnection = $dbConnection;
        $this->filesystem = $filesystem;
        $this->output = $output;
        $this->stdInHelper = $stdInHelper;
        $this->migrationBuilder = $migrationBuilder;
        $this->migrationGenerator = $migrationGenerator;
        $this->migrationRepositoryManager = $migrationRepositoryManager;
    }

    /**
     * Create new migration for selected database
     *
     * @param string $dbName
     * @param string $name
     * @param MigrationType $type
     * @param bool $isHeavyMigration
     * @param string|null $schemaName
     * @return string
     */
    public function createMigration(
        string $dbName,
        string $name,
        MigrationType $type,
        bool $isHeavyMigration = false,
        string $schemaName = null
    ): string {
        // Check database
        $this->migrationRepositoryManager->get($dbName, $type)->checkDatabase();

        return $this->migrationGenerator->generateMigration($dbName, $name, $type, $isHeavyMigration);
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
        $dbList = $dbName === null ? $this->getDatabasesWithMigrations($type) : [$dbName];
        if (count($dbList) === 0) {
            $this->output->writeln("<comment>No migrations was found</comment>");

            return;
        }

        foreach ($dbList as $db) {
            $this->output->writeln(
                "<bg=white;fg=black> --- Database '{$db}' --- </>"
            );

            $migrationsCount = 0;
            $migrationsList = $this->getMigrationsStatusList($db, $type);
            if (count($migrationsList) > 1) {
                foreach ($migrationsList as $item) {
                    if ($item->getType()->isEquals(MigrationStatusType::APPLIED)
                        || $this->isNeedToSkipMigration($item->getMigrationId(), $migrationId, $onlySingle)
                    ) {
                        continue;
                    }

                    $migrationsCount++;
                    $migration = $this->migrationBuilder->buildMigration($db, $item->getFilename(), $type);

                    $this->output->write(
                        "Starting to apply migration <comment>{$migration->getId()}</comment> ..."
                    );

                    $startedAt = microtime(true);
                    $migration->up();
                    $appliedAt = microtime(true);

                    $this->migrationRepositoryManager->get($db, $type)->store(
                        new MigrationStatus(
                            $migration->getId(),
                            $migration->getName(),
                            null,
                            null,
                            $startedAt,
                            $appliedAt
                        )
                    );

                    $this->output->writeln(
                        " <fg=green>DONE</>"
                    );
                }
            }

            if ($migrationsCount === 0) {
                $this->output->writeln("<fg=green>No new migrations was found</>");
            }
        }
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
        $this->output->writeln(
            "<bg=white;fg=black> --- Database '{$dbName}' --- </>"
        );

        $migrationsCount = 0;
        $migrationsList = $this->getMigrationsStatusList($dbName, $type);
        if (count($migrationsList) > 1) {
            krsort($migrationsList);
            foreach ($migrationsList as $item) {
                if ($item->getType()->isEquals(MigrationStatusType::NEW)
                    || $this->isNeedToSkipMigration($item->getMigrationId(), $migrationId, $onlySingle, true)
                ) {
                    continue;
                }

                $migrationsCount++;
                $migration = $this->migrationBuilder->buildMigration($dbName, $item->getFilename(), $type);

                $this->output->write(
                    "Starting to rollback migration <comment>{$migration->getId()}</comment> ..."
                );

                $migration->down();

                $this->migrationRepositoryManager->get($dbName, $type)->delete($item->getMigrationId());

                $this->output->writeln(
                    " <fg=green>DONE</>"
                );
            }
        }

        if ($migrationsCount === 0) {
            $this->output->writeln("<fg=green>No migrations for rollback was found</>");
        }
    }

    /**
     * Show migrations status
     *
     * @param MigrationType $type
     * @param string|null $dbName
     * @param string|null $migrationId
     * @return DatabaseStatusInterface[]
     */
    public function migrationsStatus(
        MigrationType $type,
        string $dbName = null,
        string $migrationId = null
    ): array {
        $result = [];

        $dbList = $dbName === null ? $this->getDatabasesWithMigrations($type) : [$dbName];
        foreach ($dbList as $name) {
            $result[] = new DatabaseStatus(
                $name,
                $type,
                $this->getMigrationsStatusList($name, $type)
            );
        }

        return $result;
    }

    /**
     * Check is we need to skip current migration
     *
     * @param string $currentId
     * @param string|null $selected
     * @param bool $onlySingle
     * @param bool $isRollback
     * @return bool
     */
    public function isNeedToSkipMigration(
        string $currentId,
        string $selected = null,
        bool $onlySingle = false,
        bool $isRollback = false
    ): bool {
        $result = false;

        $idDiff = $isRollback ? ($currentId < $selected) : ($currentId > $selected);
        if ($selected !== null) {
            if (($onlySingle === false && $idDiff)
                || ($onlySingle === true && $currentId !== $selected)
            ) {
                $result = true;
            }
        }

        return $result;
    }

    /**
     * Get list of databases with migrations
     *
     * @param MigrationType $type
     * @return string[]
     */
    public function getDatabasesWithMigrations(MigrationType $type): array
    {
        $result = [];

        $folderPath = $this->getMigrationFolderPath($type);
        $fileList = dir($folderPath);
        while (($name = $fileList->read()) !== false) {
            $filePath = $folderPath . $name;

            if (in_array($name, [".", ".."]) || is_dir($filePath) === false) {
                continue;
            }

            $result[] = $name;
        }

        return $result;
    }

    /**
     * Get migration status list
     *
     * @param string $dbName
     * @param MigrationType $type
     * @return MigrationStatusInterface[] # with migration id like key
     */
    public function getMigrationsStatusList(string $dbName, MigrationType $type): array
    {
        if ($dbName === "") {
            throw new EmptyStringException("dbName");
        }

        $result = [];
        $folderPath = $this->getMigrationFolderPath($type, $dbName);
        $migrationRepository = $this->migrationRepositoryManager->get($dbName, $type);

        $appliedMigrationsList = $migrationRepository->findMigrations();
        foreach ($appliedMigrationsList as $item) {
            $result[$item->getMigrationId()] = $item;
        }

        $fileList = dir($folderPath);
        while (($name = $fileList->read()) !== false) {
            $filePath = $folderPath . $name;

            if (in_array($name, [".", ".."]) || is_file($filePath) === false) {
                continue;
            }

            $migrationId = $this->getParsedId($name);
            if (array_key_exists($migrationId, $result) === false) {
                $result[$migrationId] = new MigrationStatus(
                    $migrationId,
                    $this->getParsedName($name),
                    $name
                );
            }
        }

        ksort($result);

        return array_values($result);
    }

    /**
     * @param MigrationType $type
     * @param string|null $dbName
     * @return string
     */
    public function getMigrationFolderPath(MigrationType $type, string $dbName = null)
    {
        if ($dbName === "") {
            throw new EmptyStringException("dbName");
        }

        $path = PROJECT_ROOT . $this->config->getDbFolderPath() . "{$type->getValue()}/"
            . ($dbName !== null ? "{$dbName}/" : "");

        if ($this->filesystem->exists($path) === false) {
            $this->filesystem->mkdir($path, self::DEFAULT_FOLDER_PERMISSIONS);
        }

        return $path;
    }

    /**
     * Get parsed migration filename
     *
     * @param string $name
     * @return string[]
     */
    public function getParsedFilename(string $name): array
    {
        if ($name === "") {
            throw new EmptyStringException("name");
        }

        preg_match(self::CLASS_NAME_REGEXP, $name, $matches);
        if (count($matches) < 3) {
            throw new \InvalidArgumentException("Invalid class name was given - {$name}");
        }

        return $matches;
    }

    /**
     * @param string $name
     * @return string
     */
    final public function getParsedId(string $name): string
    {
        return $this->getParsedFilename($name)[1];
    }

    /**
     * @param string $name
     * @return string
     */
    final public function getParsedName(string $name): string
    {
        return $this->getParsedFilename($name)[2];
    }
}
