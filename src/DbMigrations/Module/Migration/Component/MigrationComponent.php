<?php

declare(strict_types=1);

namespace DbMigrations\Module\Migration\Component;

use DbMigrations\Kernel\Component\DbConnectionInterface;
use DbMigrations\Kernel\Model\GeneralConfigInterface;
use DbMigrations\Kernel\Util\LoggerTrait;
use DbMigrations\Kernel\Util\StdInHelper;
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
     * @param string $migrationFolder
     * @param string $dataFolder
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
        string $migrationFolder,
        string $dataFolder,
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

        $this->migrationFolderPath = PROJECT_ROOT . $config->getDbFolderPath() . $migrationFolder . "/";
        $this->dataFolderPath = PROJECT_ROOT . $config->getDbFolderPath() . $dataFolder . "/";
    }
}
