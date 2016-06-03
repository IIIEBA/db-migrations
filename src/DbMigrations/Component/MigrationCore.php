<?php

namespace DbMigrations\Component;

use BaseExceptions\Exception\InvalidArgument\EmptyStringException;
use BaseExceptions\Exception\InvalidArgument\NotBooleanException;
use BaseExceptions\Exception\InvalidArgument\NotStringException;
use DbMigrations\Model\InitDbResult;
use DbMigrations\Model\InitDbResultInterface;
use DbMigrations\Model\InitTableResult;
use DbMigrations\Model\InitTableResultInterface;
use DbMigrations\Model\InitTableStatus;
use DbMigrations\Model\TableChangesAction;
use DbMigrations\Model\TableInfo;
use DbMigrations\Model\TableChanges;
use DbMigrations\Model\TableInfoInterface;
use DbMigrations\Util\PathInfo;
use PDO;
use PDOException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class MigrationCore
 * @package DbMigrations\Component
 */
class MigrationCore
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
     * MigrationCore constructor.
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

        if (is_null($logger)) {
            $logger = new NullLogger();
        }

        $this->logger = $logger;
        $this->pdo = $pdo;
        $this->schemaFolderPath = $this->detectPath($schemaFolderPath);

        $this->filesystem = new Filesystem();

        $this->logger->info("MigrationCore was successfully initialized", ["object" => $this]);
    }

    /**
     * Detect real path of for selected folder
     *
     * @param string $path
     * @return string
     */
    public function detectPath($path)
    {
        $this->logger->info("Trying to detect path for {path}", ["object" => $this, "path" => $path]);

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

        $path = rtrim($path, "/");
        $this->logger->info("Path detected as {path}", ["object" => $this, "path" => $path]);

        return $path;
    }

    /**
     * Create new migration file
     *
     * @param string $name
     * @param bool $simple
     * @return string
     */
    public function createMigration(
        $name,
        $simple = true
    ) {
        $this->logger->info("Trying to create migration with name {name}", ["object" => $this, "name" => $name]);

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

        $this->logger->info("Migration was successfully created", ["object" => $this]);

        return $generatedName;
    }

    /**
     * Init new db from schema files and add data to them from init folder if need
     *
     * @param bool $withoutData
     * @param bool $skipExists
     * @param bool $force
     * @param string|null $schemaName
     * @param string|null $schemaFolderPath
     * @return InitDbResultInterface
     */
    public function initDb(
        $withoutData = false,
        $skipExists = false,
        $force = false,
        $schemaName = null,
        $schemaFolderPath = null
    ) {
        $this->logger->info("Trying to init db", ["object" => $this]);

        if (!is_bool($force)) {
            throw new NotBooleanException("force");
        }

        if (!is_bool($skipExists)) {
            throw new NotBooleanException("skipExisted");
        }
        
        if (!is_bool($withoutData)) {
            throw new NotBooleanException("withoutData");
        }

        if (!is_null($schemaName)) {
            if (!is_string($schemaName)) {
                throw new NotStringException("schemaName");
            }
            if ($schemaName === "") {
                throw new EmptyStringException("schemaName");
            }

            $this->logger->debug(
                "Init only one table {schemaName}",
                ["object" => $this, "schemaName" => $schemaName]
            );
        }

        if (!is_null($schemaFolderPath)) {
            if (!is_string($schemaFolderPath)) {
                throw new NotStringException("schemaFolderPath");
            }
            if ($schemaFolderPath === "") {
                throw new EmptyStringException("schemaFolderPath");
            }

            $this->logger->debug(
                "Use custom schema folder path {path}",
                ["object" => $this, "path" => $schemaFolderPath]
            );
        } else {
            $this->logger->debug(
                "Use default schema folder path {path}",
                ["object" => $this, "path" => $this->schemaFolderPath]
            );

            $schemaFolderPath = $this->schemaFolderPath;
        }

        $result = new InitDbResult();

        // Create folders if not exist
        $this->createFolderIfNotExist($schemaFolderPath);

        // Apply schema files
        if (!is_null($schemaName)) {
            $selectedSchemaName = $schemaFolderPath . "/" . $schemaName . ".sql";
            if ($this->filesystem->exists($selectedSchemaName) === false) {
                throw new \LogicException("Can`t find schema with name {$schemaName}");
            }

            $schemaList = [$selectedSchemaName];
        } else {
            $schemaList = $this->getSqlFilesByPath($schemaFolderPath);
        }
        foreach ($schemaList as $elm) {
            $result->addTableResult($this->initTable($elm, $withoutData, $skipExists, $force));
        }

        $this->logger->info("Db was successfully initialized", ["object" => $this]);

        return $result;
    }

    /**
     * Execute schema file with drop syntax if need
     *
     * @param string $path
     * @param bool $withoutData
     * @param bool $skipExists
     * @param bool $force
     * @return InitTableResultInterface
     */
    public function initTable(
        $path,
        $withoutData = false,
        $skipExists = false,
        $force = false
        )
    {
        $this->logger->info(
            "Trying to create table from schema {path}",
            [
                "object" => $this,
                "path" => $path,
                "withoutData" => $withoutData,
                "skipExists" => $skipExists,
                "force" => $force
            ]
        );

        if (!is_string($path)) {
            throw new NotStringException("path");
        }
        if ($path === "") {
            throw new EmptyStringException("path");
        }

        if (!is_bool($withoutData)) {
            throw new NotBooleanException("withoutData");
        }

        if (!is_bool($skipExists)) {
            throw new NotBooleanException("skipExisted");
        }

        if (!is_bool($force)) {
            throw new NotBooleanException("force");
        }

        // Parse migration file
        $pathInfo = new PathInfo($path);
        $schemaName = $pathInfo->getFilename();
        $schemaContent = file_get_contents($path);
        if (empty($schemaContent)) {
            $msg = "Schema file `{$schemaName}` is empty";

            $this->logger->error($msg, ["object" => $this, "schemaName" => $schemaName]);
            return new InitTableResult(
                $schemaName,
                new InitTableStatus(InitTableStatus::ERROR),
                $msg
            );
        }
        $this->logger->debug("Schema {schema} was successfully parsed", ["object" => $this, "schema" => $schemaName]);

        // Check migration for create syntax and get name
        $tableName = $this->getTableNameFromSchemaPath($path);

        // Check is table exists
        $isTableExists = $this->pdo->query("SHOW TABLES LIKE '{$tableName}'")->rowCount();
        if ($isTableExists) {
            if ($skipExists) {
                $this->logger->debug(
                    "Table {tableName} is already exist, skip it",
                    ["object" => $this, "tableName" => $tableName]
                );

                return new InitTableResult(
                    $tableName,
                    new InitTableStatus(InitTableStatus::ALREADY_EXISTS)
                );
            } elseif (!$force) {
                $msg = "Table `{$tableName}` already exists";

                $this->logger->error($msg, ["object" => $this, "tableName" => $tableName]);
                return new InitTableResult(
                    $tableName,
                    new InitTableStatus(InitTableStatus::ERROR),
                    $msg
                );
            }
        }

        // Create table
        try {
            // Drop old table
            $sql = "DROP TABLE IF EXISTS `{$tableName}`";
            $dropTable = $this->pdo->exec($sql);
            $this->logger->debug(
                "Trying to drop table {tableName}",
                ["object" => $this, "tableName" => $tableName, "sql" => $sql, "result" => boolval($dropTable)]
            );

            // Create new table
            $createTable = $this->pdo->exec($schemaContent);
            $this->logger->debug(
                "Trying to create table {tableName}",
                [
                    "object" => $this,
                    "tableName" => $tableName,
                    "sql" => $schemaContent,
                    "result" => boolval($createTable)
                ]
            );
        } catch (PDOException $error) {
            $msg = "Can`t create table `{$tableName}` from schema `{$schemaName}`";

            $this->logger->error($msg, ["object" => $this, "tableName" => $tableName]);
            return new InitTableResult(
                $tableName,
                new InitTableStatus(InitTableStatus::ERROR),
                $msg
            );
        }

        // Trying to init table data
        $status = InitTableStatus::CREATED_WITHOUT_DATA;
        $pathInfo = new PathInfo($path);
        $schemaFolderPath = $pathInfo->getDirname();
        $initFilePath = $schemaFolderPath . "/" . self::INIT_DATA_FOLDER_NAME . "/" . $pathInfo->getBasename();
        if (!$withoutData && $this->filesystem->exists($initFilePath)) {
            try {
                $this->pdo->exec(file_get_contents($initFilePath));
                $status = InitTableStatus::CREATED;

                $this->logger->debug(
                    "Data for table {tableName} was successfully initialized",
                    ["object" => $this, "tableName" => $tableName]
                );
            } catch (PDOException $error) {
                $this->logger->error(
                    $error->getMessage(),
                    ["object" => $this, "exception" => $error, "tableName" => $tableName]
                );
                throw new \LogicException("Can`t init data for `{$tableName}`", 102, $error);
            }
        } else {
            $this->logger->debug("Skip init table data for {tableName}", ["object" => $this, "tableName" => $tableName]);
        }

        $this->logger->info("Table init finished for {tableName}", ["object" => $this, "tableName" => $tableName]);

        return new InitTableResult(
            $tableName,
            new InitTableStatus($status)
        );
    }

    /**
     * Get tables status with info about modifications and create syntax
     *
     * @param string|null $name
     * @return TableInfoInterface[]
     */
    public function tablesStatus($name = null) {
        if (!is_null($name)) {
            if (!is_string($name)) {
                throw new NotStringException("name");
            }
            if ($name === "") {
                throw new EmptyStringException("name");
            }
        }

        $result = [];
        $schemaTableList = [];
        $dbTableList = $this->getTableListFromDb();
        $schemaFilesList = $this->getSqlFilesByPath($this->schemaFolderPath);
        foreach ($schemaFilesList as $elmPath) {
            // Prepare some data
            $tableName = $this->getTableNameFromSchemaPath($elmPath);
            $schemaTableList[] = $tableName;
            $schemaSyntax = rtrim(trim(file_get_contents($elmPath)), ";") . ";";
            $schemaParts = explode("\n", $schemaSyntax);
            $dbSyntax = $this->getCreateSyntaxForTable($tableName);
            $dbParts = explode("\n", $this->getCreateSyntaxForTable($tableName));
            $tableInfo = new TableInfo($tableName, $schemaSyntax, $dbSyntax);

            // Check table fields if table exist in db
            if (!is_null($dbSyntax)) {
                $newFields = array_diff($schemaParts, $dbParts);
                foreach ($newFields as $field) {
                    $tableInfo->addChanges(
                        new TableChanges($field, new TableChangesAction(TableChangesAction::ADD))
                    );
                }

                $removedFields = array_diff($dbParts, $schemaParts);
                foreach ($removedFields as $field) {
                    $tableInfo->addChanges(
                        new TableChanges($field, new TableChangesAction(TableChangesAction::REMOVE))
                    );
                }
            }

            $result[$tableName] = $tableInfo;
        }

        // Build removed tables info
        $removedTables = array_diff($dbTableList, $schemaTableList);
        foreach ($removedTables as $tableName) {
            $result[$tableName] = new TableInfo($tableName, null, $this->getCreateSyntaxForTable($tableName));
        }

        // Sort tables by alphabetic
        asort($result);

        return (!is_null($name) && array_key_exists($name, $result)) ? [$result[$name]] : array_values($result);
    }

    /**
     * Chane schema syntax and get table name by schema path
     *
     * @param string $path
     * @return string
     */
    public function getTableNameFromSchemaPath($path)
    {
        $this->logger->info("Trying to get table name from schema file {path}", ["object" => $this, "path" => $path]);

        if (!is_string($path)) {
            throw new NotStringException("path");
        }
        if ($path === "") {
            throw new EmptyStringException("path");
        }

        $pathInfo = new PathInfo($path);
        if ($this->filesystem->exists($path) === false) {
            throw new \LogicException("Can`t file schema file by given path `{$pathInfo->getFilename()}`");
        }

        $schemaContent = file_get_contents($path);
        $schemaContentParts = explode("\n", $schemaContent);
        $schemaFirstLine = reset($schemaContentParts);
        if (
            preg_match(
                "~^CREATE\\s+TABLE\\s+\\`([a-z][a-zA-Z0-9]*[a-z0-9])\\`\\s+\\($~",
                $schemaFirstLine,
                $matches
            ) == false
        ) {
            throw new \LogicException(
                "Schema file `{$pathInfo->getFilename()}` must start from 'CREATE TABLE `%NAME%` (' statement"
            );
        }

        $name = end($matches);
        $this->logger->info("Table name was successfully detected - {name}", ["object" => $this, "name" => $name]);

        return $name;
    }

    /**
     * Get create table syntax
     *
     * @param string $tableName
     * @return string|null
     */
    public function getCreateSyntaxForTable($tableName)
    {
        if (!is_string($tableName)) {
            throw new NotStringException("tableName");
        }
        if ($tableName === "") {
            throw new EmptyStringException("tableName");
        }

        if (!$this->isTableExists($tableName)) {
            return null;
        }

        $sql = "SHOW CREATE TABLE `{$tableName}`";
        $createSyntax = $this->pdo->query($sql)->fetchColumn(1) . ";";
        $this->logger->debug(
            "Trying to get create syntax of table {tableName}",
            ["object" => $this, "tableName" => $tableName, "sql" => $sql, "result" => !empty($createSyntax)]
        );

        return !empty($createSyntax) ? trim(preg_replace("~AUTO_INCREMENT=\\d+\\s*~i", "", $createSyntax, 1)) : null;
    }

    /**
     * Check is table exists in db
     *
     * @param string $tableName
     * @return bool
     */
    public function isTableExists($tableName)
    {
        if (!is_string($tableName)) {
            throw new NotStringException("tableName");
        }
        if ($tableName === "") {
            throw new EmptyStringException("tableName");
        }

        $sql = "SHOW TABLES LIKE '{$tableName}'";
        $result = $this->pdo->query($sql)->rowCount();
        $this->logger->debug(
            "Trying to find table {tableName} into db",
            ["object" => $this, "tableName" => $tableName, "sql" => $sql, "count" => $result]
        );

        return boolval($result);
    }

    public function getTableListFromDb()
    {
        $sql = "SHOW TABLES";
        $result = $this->pdo->query($sql)->fetchAll(PDO::FETCH_COLUMN, 0);
        $this->logger->debug(
            "Trying to get table list from db",
            ["object" => $this, "sql" => $sql, "count" => count($result)]
        );

        return $result;
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
            $pathInfo = new PathInfo($filePath);
            if (is_dir($filePath) || $pathInfo->getExtension() !== "sql") {
                continue;
            }

            $sqlList[] = $filePath;
        }

        return $sqlList;
    }

    /**
     * Convert migration name to class name
     *
     * @param string $migrationName
     * @return string
     */
    public function convertMigrationNameToClassName($migrationName)
    {
        if (!is_string($migrationName)) {
            throw new NotStringException("migrationName");
        }
        if ($migrationName === "") {
            throw new EmptyStringException("migrationName");
        }
        
        list($date, $name) = explode("-", $migrationName, 2);
        $nameParts = explode("-", $name);

        $className = implode("", array_map(
            function ($elm) {
                return ucfirst($elm);
            },
            $nameParts
        )) . "_{$date}";

        $this->logger->info(
            "Migration name {migrationName} was successfully converted to class name - {className}",
            ["object" => $this, "migrationName" => $migrationName, "className" => $className]
        );

        return $className;
    }
}
