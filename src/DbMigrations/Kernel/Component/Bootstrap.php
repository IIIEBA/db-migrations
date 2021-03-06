<?php

declare(strict_types=1);

namespace DbMigrations\Kernel\Component;

use DbMigrations\Module\Migration\Command\Create;
use DbMigrations\Module\Migration\Command\Down;
use DbMigrations\Module\Migration\Command\Up;
use DbMigrations\Module\Migration\Command\Status as MigrationStatus;
use DbMigrations\Module\Migration\Component\MigrationBuilder;
use DbMigrations\Module\Migration\Component\MigrationBuilderInterface;
use DbMigrations\Module\Migration\Component\MigrationComponent;
use DbMigrations\Module\Migration\Component\MigrationComponentInterface;
use DbMigrations\Module\Migration\Component\MigrationGenerator;
use DbMigrations\Module\Migration\Component\MigrationGeneratorInterface;
use DbMigrations\Module\Migration\Component\MigrationRepositoryManager;
use DbMigrations\Module\Migration\Component\MigrationRepositoryManagerInterface;
use DbMigrations\Module\Migration\Enum\MigrationType;
use DbMigrations\Module\Schema\Command\Dump;
use DbMigrations\Module\Schema\Command\Init;
use DbMigrations\Module\Schema\Command\Status;
use DbMigrations\Kernel\Util\LoggerTrait;
use DbMigrations\Module\Schema\Component\SchemaComponent;
use DbMigrations\Module\Schema\Component\SchemaComponentInterface;
use DbMigrations\Kernel\Util\StdInHelper;
use DbMigrations\Module\Schema\Util\OutputFormatter;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

/**
 * Class Bootstrap
 * @package Kernel
 */
class Bootstrap
{
    use LoggerTrait;

    const CONFIG_PATH = "config/db-migrations.yml";

    /**
     * @var Application
     */
    private $application;
    /**
     * @var ArgvInput
     */
    private $input;
    /**
     * @var ConsoleOutput
     */
    private $output;
    /**
     * @var Filesystem
     */
    private $filesystem;
    /**
     * @var Yaml
     */
    private $yaml;
    /**
     * @var StdInHelper
     */
    private $stdInHelper;
    /**
     * @var DbConnection
     */
    private $dbConnection;
    /**
     * @var ConfigComponent
     */
    private $config;
    /**
     * @var SchemaComponentInterface
     */
    private $schema;
    /**
     * @var OutputFormatter
     */
    private $outputFormatter;
    /**
     * @var MigrationComponentInterface
     */
    private $migration;
    /**
     * @var MigrationBuilderInterface
     */
    private $migrationBuilder;
    /**
     * @var MigrationGeneratorInterface
     */
    private $migrationGenerator;
    /**
     * @var MigrationRepositoryManagerInterface
     */
    private $migrationRepositoryManager;

    /**
     * Bootstrap constructor.
     *
     * @param LoggerInterface|null $logger
     */
    public function __construct(LoggerInterface $logger = null)
    {
        $this->setLogger($logger);
        $this->filesystem = new Filesystem();
        $this->output = new ConsoleOutput();

        // Check config file
        $configPath = PROJECT_ROOT . self::CONFIG_PATH;
        if ($this->filesystem->exists($configPath) === false) {
            $this->output->getErrorOutput()->writeln("<error>Config file is not exists, please create it</error>");
            exit;
        }

        $this->di($configPath);
    }

    /**
     * Dependency injection method
     *
     * @param string $configPath
     */
    public function di(string $configPath)
    {
        $this->application = new Application("DB-Migration");
        $this->input = new ArgvInput();
        $this->yaml = new Yaml();
        $this->stdInHelper = new StdInHelper($this->output);
        $this->dbConnection = new DbConnection($this->getLogger());

        $this->outputFormatter = new OutputFormatter(
            $this->output,
            $this->stdInHelper,
            $this->logger
        );

        $this->config = new ConfigComponent(
            $configPath,
            $this->filesystem,
            $this->yaml,
            $this->dbConnection,
            $this->output,
            $this->getLogger()
        );

        $this->schema = new SchemaComponent(
            $this->config->getGeneralConfig(),
            $this->dbConnection,
            $this->filesystem,
            $this->output,
            $this->stdInHelper,
            $this->outputFormatter,
            $this->getLogger()
        );

        $this->migrationBuilder = new MigrationBuilder(
            $this->config->getGeneralConfig(),
            $this->dbConnection,
            $this->filesystem,
            $this->getLogger()
        );

        $this->migrationGenerator = new MigrationGenerator(
            $this->config->getGeneralConfig(),
            $this->filesystem,
            $this->getLogger()
        );

        $this->migrationRepositoryManager = new MigrationRepositoryManager(
            $this->dbConnection,
            $this->filesystem,
            $this->getLogger()
        );

        $this->migration = new MigrationComponent(
            $this->config->getGeneralConfig(),
            $this->dbConnection,
            $this->filesystem,
            $this->output,
            $this->stdInHelper,
            $this->migrationBuilder,
            $this->migrationGenerator,
            $this->migrationRepositoryManager,
            $this->getLogger()
        );
    }

    public function init()
    {
        // Schema
        $this->application->add(new Init($this->schema, $this->stdInHelper, $this->logger));
        $this->application->add(new Status($this->schema, $this->stdInHelper, $this->logger));
        $this->application->add(new Dump($this->schema, $this->stdInHelper, $this->logger));

        // Migration
        foreach (MigrationType::getList() as $value) {
            $type = new MigrationType($value);

            $this->application->add(new Create($this->migration, $this->stdInHelper, $type, $this->logger));
            $this->application->add(new Up($this->migration, $this->stdInHelper, $type, $this->logger));
            $this->application->add(new Down($this->migration, $this->stdInHelper, $type, $this->logger));
            $this->application->add(new MigrationStatus($this->migration, $this->stdInHelper, $type, $this->logger));
        }

        $this->application->run($this->input, $this->output);
    }
}
