<?php

declare(strict_types=1);

namespace DbMigrations\Kernel\Component;

use DbMigrations\Module\Schema\Command\Init;
use DbMigrations\Module\Schema\Command\Migrate;
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
     * Bootstrap constructor.
     *
     * @param LoggerInterface|null $logger
     */
    public function __construct(LoggerInterface $logger = null)
    {
        $this->setLogger($logger);
        $this->filesystem = new Filesystem();

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
        $this->output = new ConsoleOutput();
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
    }

    public function init()
    {
        $this->application->add(new Init($this->schema, $this->stdInHelper, $this->logger));
        $this->application->add(new Migrate($this->schema, $this->stdInHelper, $this->logger));
        $this->application->add(new Status($this->schema, $this->stdInHelper, $this->logger));

        $this->application->run($this->input, $this->output);
    }
}
