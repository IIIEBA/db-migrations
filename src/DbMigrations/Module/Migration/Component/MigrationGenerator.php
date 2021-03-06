<?php

declare(strict_types=1);

namespace DbMigrations\Module\Migration\Component;

use BaseExceptions\Exception\InvalidArgument\EmptyStringException;
use DbMigrations\Kernel\Exception\GeneralException;
use DbMigrations\Kernel\Model\GeneralConfigInterface;
use DbMigrations\Kernel\Util\LoggerTrait;
use DbMigrations\Module\Migration\Enum\MigrationType;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class MigrationGenerator
 * @package DbMigrations\Module\Migration\Component
 */
class MigrationGenerator implements MigrationGeneratorInterface
{
    use LoggerTrait;

    const MIGRATION_NAME_REGEXP = "/^[a-zA-Z0-9]{3,}$/";
    const MIGRATION_CLASS_NAME_PATTERN = "Migration_%s_%s";
    const MIGRATION_PLACEHOLDER_REGEXP = "/%([a-zA-Z]+)%/";
    const DEFAULT_UP_DOWN_CODE = "throw new \BaseExceptions\Exception\LogicException\NotImplementedException();";
    const DEFAULT_FOLDER_PERMISSIONS = 0775;

    /**
     * @var GeneralConfigInterface
     */
    private $config;
    /**
     * @var Filesystem
     */
    private $filesystem;
    /**
     * @var string
     */
    private $migrationTemplatePath;

    /**
     * MigrationBuilder constructor.
     *
     * @param GeneralConfigInterface $config
     * @param Filesystem $filesystem
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        GeneralConfigInterface $config,
        Filesystem $filesystem,
        LoggerInterface $logger = null
    ) {
        $this->setLogger($logger);

        $this->config = $config;
        $this->filesystem = $filesystem;
        $this->migrationTemplatePath = __DIR__ . "/../Template/Migration.txt";
    }

    /**
     * Generate new migration
     *
     * @param string $dbName
     * @param string $name
     * @param MigrationType $type
     * @param bool $isHeavyMigration
     * @return string
     * @throws GeneralException
     */
    public function generateMigration(
        string $dbName,
        string $name,
        MigrationType $type,
        bool $isHeavyMigration = false
    ): string {
        if ($dbName === "") {
            throw new EmptyStringException("dbName");
        }

        if ($name === "") {
            throw new EmptyStringException("name");
        }

        $className = $this->generateClassName($name);
        $this->createDatabaseFolderIfNotExist($dbName, $type);

        if ($this->filesystem->exists($this->migrationTemplatePath) === false) {
            throw new GeneralException("Migration template doesn`t exist by path [{$this->migrationTemplatePath}]");
        }

        // Replace placeholders
        $replacePatterns = [
            "migrationName" => $className,
            "isHeavyMigration" => intval($isHeavyMigration),
            "upCode" => self::DEFAULT_UP_DOWN_CODE,
            "downCode" => self::DEFAULT_UP_DOWN_CODE,
        ];
        $template = preg_replace_callback(
            self::MIGRATION_PLACEHOLDER_REGEXP,
            function (array $matches) use ($replacePatterns) {
                $key = end($matches);
                return array_key_exists($key, $replacePatterns) ? $replacePatterns[$key] : reset($matches);
            },
            file_get_contents($this->migrationTemplatePath)
        );

        file_put_contents(
            $this->getMigrationFolderPath($dbName, $type) . "{$className}.php",
            $template
        );

        return $className;
    }

    /**
     * Check is db folder exists and create it if not
     *
     * @param string $dbName
     * @param MigrationType $type
     */
    public function createDatabaseFolderIfNotExist(string $dbName, MigrationType $type): void
    {
        if ($dbName === "") {
            throw new EmptyStringException("dbName");
        }

        $dbFolderPath = $this->getMigrationFolderPath($dbName, $type);
        if ($this->filesystem->exists($dbFolderPath) === false) {
            $this->filesystem->mkdir($dbFolderPath, self::DEFAULT_FOLDER_PERMISSIONS);
        }
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

    /**
     * @return string
     */
    public function generateId(): string
    {
        $current = new \DateTimeImmutable();

        return $current->format("YmdHisu");
    }

    /**
     * Generate class name from migration name
     *
     * @param string $name
     * @return string
     */
    public function generateClassName(string $name): string
    {
        if (!preg_match(self::MIGRATION_NAME_REGEXP, $name)) {
            throw new \InvalidArgumentException("Invalid migration name format, allowed only [a-zA-Z0-9]");
        }

        return sprintf(
            self::MIGRATION_CLASS_NAME_PATTERN,
            $this->generateId(),
            ucfirst($name)
        );
    }
}
