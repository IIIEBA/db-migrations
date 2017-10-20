<?php

declare(strict_types=1);

namespace DbMigrations\Module\Migration\Component;

use DbMigrations\Kernel\Component\DbConnectionInterface;
use DbMigrations\Kernel\Exception\GeneralException;
use DbMigrations\Kernel\Model\GeneralConfigInterface;
use DbMigrations\Kernel\Util\LoggerTrait;
use DbMigrations\Module\Migration\Enum\MigrationType;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class MigrationBuilder
 * @package DbMigrations\Module\Migration\Component
 */
class MigrationBuilder implements MigrationBuilderInterface
{
    use LoggerTrait;

    const CLASS_NAMESPACE_REGEXP = "/\s*namespace\s*(.+);/";
    const CLASS_NAME_REGEXP = "/\s*class\s*([^\s]+)/";
    const CLASS_INTERFACE_REGEXP = "/\s*implements\s*([^\s]+)/";
    const CLASS_REQUIRED_INTERFACE = "MigrationInterface";

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
     * MigrationBuilder constructor.
     *
     * @param GeneralConfigInterface $config
     * @param DbConnectionInterface $dbConnection
     * @param Filesystem $filesystem
     * @param string $migrationFolderName
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        GeneralConfigInterface $config,
        DbConnectionInterface $dbConnection,
        Filesystem $filesystem,
        LoggerInterface $logger = null
    ) {
        $this->setLogger($logger);

        $this->config = $config;
        $this->dbConnection = $dbConnection;
        $this->filesystem = $filesystem;
    }

    /**
     * Build db migration class by name
     *
     * @param string $dbName
     * @param string $name
     * @param MigrationType $type
     * @return MigrationInterface
     * @throws GeneralException
     */
    public function buildMigration(
        string $dbName,
        string $name,
        MigrationType $type
    ): MigrationInterface {
        $migrationPath = $this->getMigrationFolderPath($dbName, $type) . "{$name}";
        if ($this->filesystem->exists($migrationPath) === false) {
            throw new GeneralException("Requested migration file - [{$name}] was not found in db - [{$dbName}]");
        }

        require_once $migrationPath;

        $className = $this->getFullClassName($migrationPath);
        if (!class_exists($className)) {
            throw new GeneralException("Cant load class [{$className}]");
        }

        return new $className(
            $this->dbConnection->getConnection($dbName),
            $this->getLogger()
        );
    }

    /**
     * Get class name with namespace
     *
     * @param string $path
     * @return string
     * @throws GeneralException
     */
    public function getFullClassName(string $path): string
    {
        if ($this->filesystem->exists($path) === false) {
            throw new GeneralException("Requested migration file was not found in path [{$path}]");
        }

        $classContent = file_get_contents($path);

        preg_match(self::CLASS_NAMESPACE_REGEXP, $classContent, $namespaceMatches);
        preg_match(self::CLASS_NAME_REGEXP, $classContent, $nameMatches);
        preg_match(self::CLASS_INTERFACE_REGEXP, $classContent, $interfaceMatches);

        if (count($namespaceMatches) !== 2
            || count($nameMatches) !== 2
            || count($interfaceMatches) !== 2
        ) {
            throw new GeneralException("Invalid format of migration in path [{$path}]");
        }

        if (end($interfaceMatches) !== self::CLASS_REQUIRED_INTERFACE) {
            throw new GeneralException(
                "Migration [" . end($nameMatches) . "] must implement ["
                . self::CLASS_REQUIRED_INTERFACE . "] interface"
            );
        }

        $className = end($namespaceMatches) . "\\" . end($nameMatches);

        return $className;
    }

    /**
     * @param string $dbName
     * @param MigrationType $type
     * @return string
     */
    public function getMigrationFolderPath(string $dbName, MigrationType $type)
    {
        return PROJECT_ROOT . $this->config->getDbFolderPath() . "{$type->getValue()}/{$dbName}/";
    }
}
