<?php

namespace DbMigrations\Component;

use BaseExceptions\Exception\InvalidArgument\EmptyStringException;
use BaseExceptions\Exception\InvalidArgument\NotStringException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class Migration
 * @package DbMigrations\Component
 */
class Migration
{
    const MIGRATIONS_FOLDER_NAME = "migrations";
    const TEMPLATE_FOLDER_PATH = __DIR__ . "/../Template";
    const CLASS_NAME_PLACEHOLDER = "%class-name%";

    /**
     * @var \PDO
     */
    private $pdo;
    /**
     * @var string
     */
    private $schemaFolderPath;
    /**
     * @var null|LoggerInterface
     */
    private $logger;
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * Migration constructor.
     * @param \PDO $pdo
     * @param string $schemaFolderPath
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        \PDO $pdo,
        $schemaFolderPath,
        LoggerInterface $logger = null
    ) {
        if (!is_string($schemaFolderPath)) {
            throw new NotStringException("schemaFolderPath");
        }
        if ($schemaFolderPath === "") {
            throw new EmptyStringException("schemaFolderPath");
        }
        
        $this->pdo = $pdo;
        $this->schemaFolderPath = rtrim($this->detectSchemaPath($schemaFolderPath), "/");
        $this->logger = $logger;

        $this->filesystem = new Filesystem();
    }

    /**
     * Detect real path of schema folder
     *
     * @param string $path
     * @return string
     */
    public function detectSchemaPath($path)
    {
        if (mb_strrpos($path, "/") !== 0) {
            $filesList = get_included_files();
            foreach ($filesList as $elm) {
                if (mb_strrpos($elm, "vendor/autoload.php") !== false) {
                    $baseDir = dirname($elm);
                    $path = realpath("{$baseDir}/../{$path}");
                    break;
                }
            }
        }

        return $path;
    }

    /**
     * Create new migration file
     *
     * @param string $name
     * @param bool $simple
     * @return string
     */
    public function createMigration($name, $simple = true)
    {
        if (!is_string($name)) {
            throw new NotStringException("name");
        }
        if ($name === "") {
            throw new EmptyStringException("name");
        }
        if (!preg_match("/[a-zA-Z0-9-]+$/", $name)) {
            throw new \InvalidArgumentException("Name must match [a-zA-Z0-9-]+");
        }

        // Create need folders if they not exists
        $this->createFolderIfNotExist($this->schemaFolderPath);
        $migrationsFolderPath = $this->schemaFolderPath . "/" . self::MIGRATIONS_FOLDER_NAME;
        $this->createFolderIfNotExist($migrationsFolderPath);


        $pattern = file_get_contents(
            self::TEMPLATE_FOLDER_PATH . "/migration-" . ($simple ? "simple" : "safe") . ".tpl"
        );
        $generatedName = date("Ymd-") . $name;

        file_put_contents(
            $migrationsFolderPath . "/" . $generatedName . ".php",
            str_replace(self::CLASS_NAME_PLACEHOLDER, $this->convertMigrationNameToClassName($generatedName), $pattern)
        );

        return $generatedName;
    }

    /**
     * Create folder by given path if not exist
     *
     * @param string $path
     */
    public function createFolderIfNotExist($path)
    {
        if (!is_string($path)) {
            throw new NotStringException("path");
        }
        if ($path === "") {
            throw new EmptyStringException("path");
        }

        if ($this->filesystem->exists($path) === false) {
            $this->filesystem->mkdir($path);
        }
    }

    /**
     * Convert migration name to class name
     *
     * @param string $name
     * @return string
     */
    public function convertMigrationNameToClassName($name)
    {
        if (!is_string($name)) {
            throw new NotStringException("name");
        }
        if ($name === "") {
            throw new EmptyStringException("name");
        }
        
        list($date, $name) = explode("-", $name, 2);
        $nameParts = explode("-", $name);
        
        return implode("", array_map(
            function ($elm) {
                return ucfirst($elm);
            },
            $nameParts
        )) . "_{$date}";
    }
}
