<?php

namespace DbMigrations\Component;

use BaseExceptions\Exception\InvalidArgument\EmptyStringException;
use BaseExceptions\Exception\InvalidArgument\NotBooleanException;
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
    const MIGRATIONS_LOG_FILE_NAME = ".migrations-log.yml";
    const INIT_DATA_FOLDER_NAME = "init";
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
     * @var LoggerInterface
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
        $this->schemaFolderPath = $this->detectPath($schemaFolderPath);
        $this->logger = $logger;

        $this->filesystem = new Filesystem();
    }

    /**
     * Detect real path of for selected folder
     *
     * @param string $path
     * @return string
     */
    public function detectPath($path)
    {
        if (mb_strrpos($path, "/") !== 0) {
            $filesList = get_included_files();
            foreach ($filesList as $elm) {
                if (mb_strrpos($elm, "vendor/autoload.php") !== false) {
                    $baseDir = dirname($elm);
                    $path = "{$baseDir}/../{$path}";
                    break;
                }
            }
        }

        return rtrim($path, "/");
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
        if (!preg_match("~^[a-z0-9-]+[a-z0-9]+$~is", $name)) {
            throw new \InvalidArgumentException("Name must match ^[a-zA-Z0-9-]+[a-zA-Z0-9]+");
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
     * Init new db from schema files and add data to them from init folder if need
     * 
     * @param bool $withoutData
     * @param bool $force
     * @param string|null $schemaFolderPath
     * @return array
     */
    public function initDb(
        $withoutData = false,
        $force = false,
        $schemaFolderPath = null
    ) {
        if (!is_bool($force)) {
            throw new NotBooleanException("force");
        }
        
        if (!is_bool($withoutData)) {
            throw new NotBooleanException("withoutData");
        }
        
        if (!is_null($schemaFolderPath)) {
            if (!is_string($schemaFolderPath)) {
                throw new NotStringException("schemaFolderPath");
            }
            if ($schemaFolderPath === "") {
                throw new EmptyStringException("schemaFolderPath");
            }
        } else {
            $schemaFolderPath = $this->schemaFolderPath;
        }

        // Create folders if not exist
        $this->createFolderIfNotExist($schemaFolderPath);
        $migrationsFolderPath = $schemaFolderPath . "/" . self::MIGRATIONS_FOLDER_NAME;
        $this->createFolderIfNotExist($migrationsFolderPath);
        $initFolderPath = $schemaFolderPath . "/" . self::INIT_DATA_FOLDER_NAME;

        // Create migrations log file if not exist
        $migrationLogFilePath = $migrationsFolderPath . "/" . self::MIGRATIONS_LOG_FILE_NAME;
        if ($this->filesystem->exists($migrationLogFilePath) === false) {
            $this->filesystem->touch($migrationLogFilePath);
        }

        // Apply schema files
        $schemaList = $this->getSqlFilesByPath($schemaFolderPath);
        foreach ($schemaList as $path) {
            $name = pathinfo($path, PATHINFO_FILENAME);

            if ($this->executeSchema($path, $force) === false) {
                throw new \LogicException("Can`t execute {$name} schema");
            }
        }

        // Init tables data if need
        if ($withoutData) {
            $initList = $this->filesystem->exists($initFolderPath) ? $this->getSqlFilesByPath($initFolderPath) : [];
            // TODO: add init data statement
        }

        return [];
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
     * Execute schema file with drop syntax if need
     *
     * @param string $path
     * @param bool $recreate
     * @return bool
     */
    public function executeSchema($path, $recreate = false)
    {
        if (!is_string($path)) {
            throw new NotStringException("path");
        }
        if ($path === "") {
            throw new EmptyStringException("path");
        }

        if (!is_bool($recreate)) {
            throw new NotBooleanException("recreate");
        }

        if ($recreate) {
            // TODO: add drop table syntax
        }

        return $this->pdo->exec(file_get_contents($path)) !== false;
    }

    /**
     * Get list of SQL files in given path
     *
     * @param string $path
     * @return string[]
     */
    public function getSqlFilesByPath($path)
    {
        if (!is_string($path)) {
            throw new NotStringException("path");
        }
        if ($path === "") {
            throw new EmptyStringException("path");
        }

        $sqlList = [];
        $filesList = scandir($path);
        foreach ($filesList as $name) {
            $filePath = $path . "/{$name}";

            // Skip directories and not SQL files
            if (is_dir($filePath) || pathinfo($filePath, PATHINFO_EXTENSION) !== "sql") {
                continue;
            }

            $sqlList[] = $filePath;
        }

        return $sqlList;
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
