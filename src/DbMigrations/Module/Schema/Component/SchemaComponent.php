<?php

declare(strict_types=1);

namespace DbMigrations\Module\Schema\Component;

use BaseExceptions\Exception\InvalidArgument\EmptyStringException;
use DbMigrations\Kernel\Component\DbConnectionInterface;
use DbMigrations\Kernel\Exception\GeneralException;
use DbMigrations\Kernel\Model\GeneralConfigInterface;
use DbMigrations\Kernel\Util\LoggerTrait;
use DbMigrations\Kernel\Util\StdInHelper;
use DbMigrations\Module\Schema\Enum\TableChangesAction;
use DbMigrations\Module\Schema\Enum\DbInfoStatus;
use DbMigrations\Module\Schema\Enum\TableRowType;
use DbMigrations\Module\Schema\Model\Database;
use DbMigrations\Module\Schema\Model\DatabaseInfo;
use DbMigrations\Module\Schema\Model\DatabaseInfoInterface;
use DbMigrations\Module\Schema\Model\DatabaseInterface;
use DbMigrations\Module\Schema\Model\Table;
use DbMigrations\Module\Schema\Model\TableChanges;
use DbMigrations\Module\Schema\Model\TableInfo;
use DbMigrations\Module\Schema\Model\TableInfoInterface;
use DbMigrations\Module\Schema\Model\TableRow;
use DbMigrations\Module\Schema\Model\TableRowInterface;
use DbMigrations\Module\Schema\Util\OutputFormatter;
use PDO;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class SchemaComponent
 * @package Module\Schema\Command
 */
class SchemaComponent implements SchemaComponentInterface
{
    use LoggerTrait;

    const SCHEMA_FOLDER = "schema";
    const DEFAULT_FOLDER_PERMISSIONS = 0755;
    const SYSTEM_TABLES_REGEXP = "/^_db_.+$/";

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
    private $schemaFolderPath;
    /**
     * @var OutputFormatter
     */
    private $outputFormatter;

    /**
     * SchemaComponent constructor.
     *
     * @param GeneralConfigInterface $config
     * @param DbConnectionInterface $dbConnection
     * @param Filesystem $filesystem
     * @param OutputInterface $output
     * @param StdInHelper $stdInHelper
     * @param OutputFormatter $outputFormatter
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        GeneralConfigInterface $config,
        DbConnectionInterface $dbConnection,
        Filesystem $filesystem,
        OutputInterface $output,
        StdInHelper $stdInHelper,
        OutputFormatter $outputFormatter,
        LoggerInterface $logger = null
    ) {
        $this->setLogger($logger);

        $this->config = $config;
        $this->dbConnection = $dbConnection;
        $this->filesystem = $filesystem;
        $this->output = $output;
        $this->stdInHelper = $stdInHelper;

        $this->schemaFolderPath = PROJECT_ROOT . $config->getDbFolderPath() . self::SCHEMA_FOLDER . "/";
        $this->outputFormatter = $outputFormatter;
    }

    /**
     * Show db status
     *
     * @param string|null $database
     * @param string|null $tableName
     * @return void
     */
    public function showStatus(
        string $database = null,
        string $tableName = null
    ): void {
        $status = $this->getDbStatus($database, $tableName);

        foreach ($status as $db) {
            switch (true) {
                case $db->getStatus()->isEquals(DbInfoStatus::ACTUAL):
                    $this->output->writeln(
                        PHP_EOL . "<bg=white;fg=black> --- Database '{$db->getName()}' is actual --- </>"
                    );

                    break;

                case $db->getStatus()->isEquals(DbInfoStatus::MODIFIED):
                    $this->output->writeln(
                        PHP_EOL . "<bg=yellow;fg=black> --- Database '{$db->getName()}' is modified --- </>"
                    );

                    break;

                case $db->getStatus()->isEquals(DbInfoStatus::CREATED):
                    $this->output->writeln(
                        PHP_EOL . "<bg=green;fg=black> --- Database '{$db->getName()}' is not created --- </>"
                    );

                    break;

                case $db->getStatus()->isEquals(DbInfoStatus::REMOVED):
                    $this->output->writeln(
                        PHP_EOL
                        . "<error> --- Requested database '{$db->getName()}' was not found in schema --- </error>"
                    );

                    continue 2;
            }

            if ($tableName !== null && count($db->getTableList()) === 0) {
                $this->output->writeln(
                    PHP_EOL . "<error> --- Requested table '{$tableName}' was not found --- </error>" . PHP_EOL
                );
            }

            foreach ($db->getTableList() as $table) {
                $this->outputFormatter->showTableNameForStatus($table);
                $this->outputFormatter->showModifiedFields($table);
                $this->outputFormatter->showCreateTableSyntax($table);
            }
        }

        $this->output->writeln("");
    }

    /**
     * Init databases
     *
     * @param string|null $database
     * @param string|null $tableName
     * @param bool $withOutData
     */
    public function initDb(
        string $database = null,
        string $tableName = null,
        bool $withOutData = false
    ): void {
        $status = $this->getDbStatus($database, $tableName);

        foreach ($status as $db) {
            switch (true) {
                case $db->getStatus()->isEquals(DbInfoStatus::ACTUAL):
                    $this->output->writeln(
                        PHP_EOL . "<bg=white;fg=black> --- Database '{$db->getName()}' is actual --- </>"
                    );

                    continue 2;

                case $db->getStatus()->isEquals(DbInfoStatus::MODIFIED):
                    $this->output->writeln(
                        PHP_EOL . "<bg=yellow;fg=black> --- Database '{$db->getName()}' is modified --- </>"
                    );

                    break;

                case $db->getStatus()->isEquals(DbInfoStatus::CREATED):
                    $this->createDatabase($db->getName());
                    $this->output->writeln(
                        PHP_EOL
                        . "<bg=green;fg=black> --- Database '{$db->getName()}' was successfully created --- </>"
                    );

                    break;

                case $db->getStatus()->isEquals(DbInfoStatus::REMOVED):
                    $status = $this->stdInHelper->confirm(
                        "Database '{$db->getName()}' was not found in schema, remove it?"
                    );

                    if ($status) {
                        $this->deleteDatabase($db->getName());
                        $this->output->writeln(
                            PHP_EOL .
                            "<bg=green;fg=black> --- Database '{$db->getName()}' was successfully deleted --- </>"
                        );
                    } else {
                        $this->output->writeln(
                            PHP_EOL .
                            "<bg=red;fg=white> --- Database '{$db->getName()}' was not found in schema --- </>"
                        );
                    }

                    continue 2;
            }

            foreach ($db->getTableList() as $table) {
                switch (true) {
                    case $table->getStatus()->isEquals(DbInfoStatus::ACTUAL):
                        $this->outputFormatter->showTableName(
                            $table->getTableName(),
                            "is equal",
                            $table->getStatus()
                        );
                        $this->outputFormatter->showCreateTableSyntax($table, true);

                        break;

                    case $table->getStatus()->isEquals(DbInfoStatus::MODIFIED):
                        $status = $this->stdInHelper->confirm(
                            "Table '{$table->getTableName()}' was modified, recreate it?"
                        );

                        if ($status) {
                            $this->createTable($db->getName(), $table->getSchemaSyntax(), true);

                            $this->outputFormatter->showTableName(
                                $table->getTableName(),
                                "was successfully recreated",
                                $table->getStatus()
                            );
                        }
                        $this->outputFormatter->showCreateTableSyntax($table, true);

                        break;

                    case $table->getStatus()->isEquals(DbInfoStatus::CREATED):
                        $this->createTable($db->getName(), $table->getSchemaSyntax());

                        $this->outputFormatter->showTableName(
                            $table->getTableName(),
                            "was successfully recreated",
                            $table->getStatus()
                        );
                        $this->outputFormatter->showCreateTableSyntax($table, true);

                        break;

                    case $table->getStatus()->isEquals(DbInfoStatus::REMOVED):
                        $status = $this->stdInHelper->confirm(
                            "Table '{$table->getTableName()}' was removed from schema, delete it?"
                        );

                        if ($status) {
                            $this->deleteTable($db->getName(), $table->getTableName());

                            $this->outputFormatter->showTableName(
                                $table->getTableName(),
                                "was successfully removed",
                                $table->getStatus()
                            );
                            $this->outputFormatter->showCreateTableSyntax($table, true);
                        }

                        break;
                }
            }
        }

        $this->output->writeln("");
    }

    /**
     * Dump database to schema files
     *
     * @param string|null $database
     * @param string|null $tableName
     * @throws GeneralException
     */
    public function dumpDb(string $database = null, string $tableName = null): void
    {
        $dbList = $database !== null ? [$database] :$this->dbConnection->getConnectionNamesList();
        if (empty($dbList)) {
            throw new GeneralException("No databases was via connection names, try to select database manually");
        }

        foreach ($dbList as $dbName) {
            list($db) = $this->getDbStatus($dbName, $tableName);
            switch (true) {
                case $db->getStatus()->isEquals(DbInfoStatus::ACTUAL):
                    $this->output->writeln(
                        PHP_EOL . "<bg=white;fg=black> --- Database '{$db->getName()}' is actual --- </>"
                    );

                    continue;

                case $db->getStatus()->isEquals(DbInfoStatus::MODIFIED):
                    $this->output->writeln(
                        PHP_EOL . "<bg=yellow;fg=black> --- Database '{$db->getName()}' is modified --- </>"
                    );

                    break;

                case $db->getStatus()->isEquals(DbInfoStatus::CREATED):
                    $this->output->writeln(
                        PHP_EOL
                        . "<bg=red;fg=white> --- Database '{$db->getName()}' was not found --- </>"
                    );

                    continue;

                case $db->getStatus()->isEquals(DbInfoStatus::REMOVED):
                    $this->output->writeln(
                        PHP_EOL .
                        "<bg=yellow;fg=black> --- Database '{$db->getName()}' is missing in schema, dumping it --- </>"
                    );

                    break;
            }

            foreach ($db->getTableList() as $table) {
                switch (true) {
                    case $table->getStatus()->isEquals(DbInfoStatus::ACTUAL):
                        $this->outputFormatter->showTableName(
                            $table->getTableName(),
                            "is equal",
                            $table->getStatus()
                        );
                        $this->outputFormatter->showCreateTableSyntax($table, true);

                        break;

                    case $table->getStatus()->isEquals(DbInfoStatus::MODIFIED):
                        $status = $this->stdInHelper->confirm(
                            "Table '{$table->getTableName()}' was modified, recreate it?"
                        );
                        if ($status) {
                            $this->createSchema($db->getName(), $table->getTableName(), $table->getDbSyntax());

                            $this->outputFormatter->showTableName(
                                $table->getTableName(),
                                "was successfully recreated",
                                $table->getStatus()
                            );
                            $this->outputFormatter->showCreateTableSyntax($table, true);
                        }

                        break;

                    // Table is missing in db but exists in schema files
                    case $table->getStatus()->isEquals(DbInfoStatus::CREATED):
                        $status = $this->stdInHelper->confirm(
                            "Table '{$table->getTableName()}' was removed, delete schema?"
                        );
                        if ($status) {
                            $this->deleteSchema($db->getName(), $table->getTableName());

                            $this->outputFormatter->showTableName(
                                $table->getTableName(),
                                "was successfully removed",
                                new DbInfoStatus(DbInfoStatus::REMOVED)
                            );
                            $this->outputFormatter->showCreateTableSyntax($table, true);
                        }

                        break;

                    // Table is created in db but missing schema file
                    case $table->getStatus()->isEquals(DbInfoStatus::REMOVED):
                        $this->createSchema($db->getName(), $table->getTableName(), $table->getDbSyntax());

                        $this->outputFormatter->showTableName(
                            $table->getTableName(),
                            "was successfully created",
                            new DbInfoStatus(DbInfoStatus::CREATED)
                        );
                        $this->outputFormatter->showCreateTableSyntax($table, true);

                        break;
                }
            }
        }

        $this->output->writeln("");
    }

    /**
     * Put to output db status
     *
     * @param string|null $database
     * @param string|null $tableName
     * @return DatabaseInfoInterface[]
     */
    public function getDbStatus(string $database = null, string $tableName = null): array
    {
        if ($database !== null && $database === "") {
            throw new EmptyStringException("database");
        }

        if ($tableName !== null && $tableName === "") {
            throw new EmptyStringException("tableName");
        }

        $dbList = [];
        $dbFound = false;

        $dbLocalSchemaList = $this->parseLocalSchemas();
        foreach ($dbLocalSchemaList as $db) {
            $dbExists = $this->isDatabaseExists($db->getName());

            // Skip db if not match requested name
            if ($database !== null && $db->getName() !== $database) {
                continue;
            }

            $dbFound = true;
            $schemaTableNames = [];
            $dbTableNames = $this->getTableListFromDb($db->getName());

            $tableList = [];
            foreach ($db->getSchemaList() as $schema) {
                $schemaTableNames[] = $schema->getName();

                // Skip table if not match requested name
                if ($tableName !== null && $schema->getName() !== $tableName) {
                    continue;
                }

                // Set some variables
                $dbSchema = $this->getCreateSyntaxForTable($db->getName(), $schema->getName());
                $tableInfo = new TableInfo(
                    $schema->getName(),
                    $schema->getSchema(),
                    $dbSchema
                );

                $this->checkSchemaDiff($schema->getSchema(), $tableInfo, $dbSchema);

                $tableList[$schema->getName()] = $tableInfo;
            }

            // Parse removed tables
            $removedTables = array_udiff($dbTableNames, $schemaTableNames, [$this, "matchFields"]);
            foreach ($removedTables as $name) {
                // Skip table if not match requested name
                if ($tableName !== null && $name !== $tableName) {
                    continue;
                }

                $tableList[$name] = new TableInfo(
                    $name,
                    null,
                    $this->getCreateSyntaxForTable($db->getName(), $name)
                );
            }

            // Create result that requested table is not found
            if ($tableName !== null && count($tableList) === 0) {
                $dbList[] = new DatabaseInfo(
                    $db->getName(),
                    [],
                    new DbInfoStatus(DbInfoStatus::MODIFIED)
                );

                continue;
            }

            // Check db status
            $modified = false;
            foreach ($tableList as $elm) {
                if ($elm->getStatus()->isEquals(DbInfoStatus::ACTUAL) === false) {
                    $modified = true;
                    break;
                }
            }

            sort($tableList);
            $dbList[] = new DatabaseInfo(
                $db->getName(),
                array_values($tableList),
                $dbExists
                    ? ($modified ? new DbInfoStatus(DbInfoStatus::MODIFIED) :new DbInfoStatus(DbInfoStatus::ACTUAL))
                    : new DbInfoStatus(DbInfoStatus::CREATED)
            );
        }

        // Check db not from schema file
        if ($database !== null && $dbFound === false) {
            $tableList = [];
            $tableNames = $this->getTableListFromDb($database);
            foreach ($tableNames as $name) {
                $tableList[] = new TableInfo(
                    $name,
                    null,
                    $this->getCreateSyntaxForTable($database, $name)
                );
            }

            $dbList[] = new DatabaseInfo(
                $database,
                $tableList,
                new DbInfoStatus(DbInfoStatus::REMOVED)
            );
        }

        return $dbList;
    }

    /**
     * Check schema diff
     *
     * @param string $localSchema
     * @param TableInfoInterface $tableInfo
     * @param string|null $dbSchema
     */
    public function checkSchemaDiff(
        string $localSchema,
        TableInfoInterface $tableInfo,
        string $dbSchema = null
    ): void {
        // Check table fields if table exist in db
        if (!is_null($dbSchema)) {
            $localSchemaParts = explode("\n", $localSchema);
            $dbSchemaParts = explode("\n", $dbSchema);

            // Build difference
            $newRows = array_udiff($localSchemaParts, $dbSchemaParts, [$this, "matchFields"]);
            $removedRows = array_udiff($dbSchemaParts, $localSchemaParts, [$this, "matchFields"]);

            // Parse rows
            /**
             * @var TableRowInterface[] $newParsedRows
             * @var TableRowInterface[] $removedParsedRows
             */
            $newParsedRows = [];
            $removedParsedRows = [];
            foreach ($newRows as $id => $row) {
                $parsed = $this->parseSchemaRow($row);

                // Get new row location
                $location = $this->getNewRowLocation(
                    $localSchemaParts,
                    $id,
                    $parsed->getType()
                );
                if ($location !== null) {
                    $parsed = $parsed->setLocation($location);
                }

                $key = $parsed->getType()->getValue() . "_" . $parsed->getName();
                $newParsedRows[$key] = $parsed;
            }
            foreach ($removedRows as $row) {
                $parsed = $this->parseSchemaRow($row);
                $key = $parsed->getType()->getValue() . "_" . $parsed->getName();
                $removedParsedRows[$key] = $parsed;
            }

            // Check difference
            foreach ($newParsedRows as $key => $row) {
                if (array_key_exists($key, $removedParsedRows)) {
                    $tableInfo->addChanges(
                        new TableChanges($row->getRow(), new TableChangesAction(TableChangesAction::MODIFIED))
                    );

                    unset($removedParsedRows[$key]);
                } else {
                    $tableInfo->addChanges(
                        new TableChanges(
                            $row->getType()->isEquals(TableRowType::COLUMN)
                                ? $row->getPreparedRow() : $row->getName(),
                            new TableChangesAction(TableChangesAction::ADD)
                        )
                    );
                }
            }
            foreach ($removedParsedRows as $row) {
                $tableInfo->addChanges(
                    new TableChanges($row->getRow(), new TableChangesAction(TableChangesAction::REMOVE))
                );
            }
        }
    }

    /**
     * Parse schema row object with type
     *
     * @param string $row
     * @return TableRowInterface
     */
    public function parseSchemaRow(string $row): TableRowInterface
    {
        $row = rtrim(trim($row), ",");

        switch (true) {
            // Column
            case preg_match("/^\s*(\`(.+)\`\s.+)$/", $row, $matches):
                $result = new TableRow(
                    $matches[1],
                    new TableRowType(TableRowType::COLUMN),
                    $matches[2]
                );
                break;

            // Index
            case preg_match("/^\s*(.*KEY\s\(?\`([^\`\)]+)\`\)?.*)$/", $row, $matches):
                $result = new TableRow(
                    $matches[1],
                    new TableRowType(TableRowType::KEY),
                    $matches[2]
                );
                break;

            default:
                $result = new TableRow($row, new TableRowType(TableRowType::KEY));
        }

        return $result;
    }

    /**
     * Get new row location in table
     *
     * @param string[] $schemaParts
     * @param int $currentId
     * @param TableRowType $type
     * @return null|string
     */
    public function getNewRowLocation(
        array $schemaParts,
        int $currentId,
        TableRowType $type
    ) :? string {
        $result = "FIRST";
        $prevId = --$currentId;

        if ($prevId > 0) {
            $row = $this->parseSchemaRow($schemaParts[$prevId]);
            if ($row->getType()->isEquals($type) && $row->getName() !== null) {
                $result = "AFTER `" . $row->getName() . "`";
            }
        }

        return $result;
    }

    /**
     * Compare left and right
     *
     * @param mixed $left
     * @param mixed $right
     * @return int
     */
    public function matchFields($left, $right): int
    {
        $left = rtrim(trim($left), ",");
        if (empty($left)) {
            return 0;
        }

        return strcasecmp($left, rtrim(trim($right), ","));
    }

    /**
     * Parse local schemas
     *
     * @return DatabaseInterface[]
     * @throws GeneralException
     */
    public function parseLocalSchemas(): array
    {
        if ($this->filesystem->exists($this->schemaFolderPath) === false) {
            $this->filesystem->mkdir($this->schemaFolderPath, self::DEFAULT_FOLDER_PERMISSIONS);
        }

        $result = [];
        $dbList = scandir($this->schemaFolderPath);
        foreach ($dbList as $dbName) {
            $folderPath = $this->schemaFolderPath . "{$dbName}/";
            if (is_dir($folderPath) === false || in_array($dbName, [".", ".."])) {
                continue;
            }

            $schemaModelList = [];
            $schemaList = scandir($folderPath);
            foreach ($schemaList as $schemaName) {
                $filePath = $folderPath . $schemaName;
                if (is_file($filePath) === false || !preg_match("/^(.+)\.sql$/", $schemaName)) {
                    continue;
                }

                $schema = file_get_contents($filePath);
                $schemaModelList[] = new Table(
                    $this->getTableNameFromSchema($schema),
                    $schema,
                    $filePath
                );
            }

            $result[] = new Database(
                $dbName,
                $schemaModelList
            );
        }

        return $result;
    }

    /**
     * Get create table syntax
     *
     * @param string $database
     * @param string $tableName
     * @return null|string
     */
    public function getCreateSyntaxForTable(string $database, string $tableName): ?string
    {
        if ($database === "") {
            throw new EmptyStringException("database");
        }

        if ($tableName === "") {
            throw new EmptyStringException("tableName");
        }

        if (!$this->isDatabaseExists($database)) {
            return null;
        }

        if (!$this->isTableExists($database, $tableName)) {
            return null;
        }

        $sql = "SHOW CREATE TABLE `{$database}`.`{$tableName}`";
        $createSyntax = $this->dbConnection->getConnection($database)->query($sql)->fetchColumn(1) . ";";
        $this->logger->debug(
            "Trying to get create syntax for table {tableName} in db {database}",
            [
                "object" => $this,
                "tableName" => $tableName,
                "sql" => $sql,
                "result" => !empty($createSyntax),
                "database" => $database
            ]
        );

        return !empty($createSyntax) ? trim(preg_replace("~AUTO_INCREMENT=\\d+\\s*~i", "", $createSyntax, 1)) : null;
    }

    /**
     * Create table from schema
     *
     * @param string $database
     * @param string $schema
     * @param bool $force
     * @throws GeneralException
     */
    public function createTable(string $database, string $schema, bool $force = false): void
    {
        if ($database === "") {
            throw new EmptyStringException("database");
        }

        if ($schema === "") {
            throw new EmptyStringException("schema");
        }

        $tableName = $this->getTableNameFromSchema($schema);
        if ($this->isTableExists($database, $tableName)) {
            if ($force !== true) {
                throw new GeneralException("Cant create table '{$database}' in db -{$database}, already exists");
            }

            $this->deleteTable($database, $tableName);
        }

        $this->dbConnection->getConnection($database)->exec(
            $this->addDatabaseToSchema($database, $schema)
        );
        $this->logger->debug(
            "Successfully created table {$tableName} in db {database}",
            ["object" => $this, "sql" => $schema, "database" => $database]
        );
    }

    /**
     * Delete requested table
     *
     * @param string $database
     * @param string $tableName
     * @throws GeneralException
     */
    public function deleteTable(string $database, string $tableName): void
    {
        if ($database === "") {
            throw new EmptyStringException("database");
        }

        if ($tableName === "") {
            throw new EmptyStringException("tableName");
        }

        if ($this->isTableExists($database, $tableName) === false) {
            throw new GeneralException("Requested table '{$tableName}' is not exists in db '{$database}'");
        }

        $sql = "DROP TABLE `{$database}`.`{$tableName}`";
        $this->dbConnection->getConnection($database)->exec($sql);
        $this->logger->debug(
            "Successfully removed table {table} in db {database}",
            ["object" => $this, "sql" => $sql, "database" => $database, "table" => $tableName]
        );
    }

    /**
     * Create new database
     *
     * @param string $database
     * @param bool $force
     * @throws GeneralException
     */
    public function createDatabase(string $database, bool $force = false): void
    {
        if ($database === "") {
            throw new EmptyStringException("database");
        }

        if ($this->isDatabaseExists($database)) {
            if ($force !== true) {
                throw new GeneralException("Cant create database '{$database}', already exists");
            }

            $this->deleteDatabase($database);
        }

        $sql = "CREATE DATABASE `{$database}`";
        $this->dbConnection->getConnection($database)->exec($sql);
        $this->logger->debug(
            "Successfully created db {database}",
            ["object" => $this, "sql" => $sql, "database" => $database]
        );
    }

    /**
     * Delete selected database
     *
     * @param string $database
     * @throws GeneralException
     */
    public function deleteDatabase(string $database): void
    {
        if ($database === "") {
            throw new EmptyStringException("database");
        }

        if ($this->isDatabaseExists($database)) {
            $sql = "DROP DATABASE {$database}";
            $this->dbConnection->getConnection($database)->exec($sql);
            $this->logger->debug(
                "Successfully removed db {database}",
                ["object" => $this, "sql" => $sql, "database" => $database]
            );
        }
    }

    /**
     * Check is database exists
     *
     * @param string $database
     * @return bool
     */
    public function isDatabaseExists(string $database): bool
    {
        if ($database === "") {
            throw new EmptyStringException("database");
        }

        $sql = "SHOW DATABASES LIKE '{$database}'";
        $result = $this->dbConnection->getConnection($database)->query($sql)->rowCount();
        $this->logger->debug(
            "Trying to find database {database}",
            ["object" => $this, "sql" => $sql, "count" => $result, "database" => $database]
        );

        return boolval($result);
    }

    /**
     * Check is table exists in db
     *
     * @param string $database
     * @param string $tableName
     * @return bool
     */
    public function isTableExists(string $database, string $tableName): bool
    {
        if ($database === "") {
            throw new EmptyStringException("database");
        }

        if ($tableName === "") {
            throw new EmptyStringException("tableName");
        }

        $sql = "SHOW TABLES IN {$database} LIKE '{$tableName}'";
        $result = $this->dbConnection->getConnection($database)->query($sql)->rowCount();
        $this->logger->debug(
            "Trying to find table {tableName} into db {database}",
            ["object" => $this, "tableName" => $tableName, "sql" => $sql, "count" => $result, "database" => $database]
        );

        return boolval($result);
    }

    /**
     * Get list of all tables in db
     *
     * @param string $database
     * @return string[]
     */
    public function getTableListFromDb(string $database): array
    {
        if ($database === "") {
            throw new EmptyStringException("database");
        }

        $result = [];
        if ($this->isDatabaseExists($database)) {
            $sql = "SHOW TABLES IN {$database}";
            $result = $this->dbConnection->getConnection($database)->query($sql)->fetchAll(PDO::FETCH_COLUMN, 0);
            $this->logger->debug(
                "Trying to get table list from db {database}",
                ["object" => $this, "sql" => $sql, "count" => count($result), "database" => $database]
            );
        }

        // Remove system tables from this list
        return array_filter(
            $result,
            function ($name) {
                return !preg_match(self::SYSTEM_TABLES_REGEXP, $name);
            }
        );
    }

    /**
     * Get table name from schema6
     *
     * @param string $schema
     * @return string
     * @throws GeneralException
     */
    public function getTableNameFromSchema(string $schema): string
    {
        if ($schema === "") {
            throw new EmptyStringException("schema");
        }

        list($firstLine) = explode("\n", $schema);
        if (preg_match("/^CREATE\s+TABLE\s+\`([a-zA-Z0-9-_\.]+)\`\s+\($/", $firstLine, $matches) === false) {
            throw new GeneralException("Invalid SQL file format");
        }

        return end($matches);
    }

    /**
     * Add database name to schema
     *
     * @param string $database
     * @param string $schema
     * @return string
     */
    public function addDatabaseToSchema(string $database, string $schema): string
    {
        if ($database === "") {
            throw new EmptyStringException("database");
        }

        if ($schema === "") {
            throw new EmptyStringException("schema");
        }

        return preg_replace_callback(
            "/^(CREATE\s+TABLE\s+)\`([a-zA-Z0-9-_\.]+)\`(\s+\()/",
            function ($matches) use ($database) {
                return  "{$matches[1]}`{$database}`.`{$matches[2]}`{$matches[3]}";
            },
            $schema
        );
    }

    /**
     * Create schema in database directory
     *
     * @param string $database
     * @param string $tableName
     * @param string $syntax
     */
    public function createSchema(
        string $database,
        string $tableName,
        string $syntax
    ): void {
        if ($database === "") {
            throw new EmptyStringException("database");
        }

        if ($tableName === "") {
            throw new EmptyStringException("tableName");
        }

        if ($syntax === "") {
            throw new EmptyStringException("syntax");
        }

        $this->createDatabaseFolder($database);

        $path = $this->schemaFolderPath . "{$database}/{$tableName}.sql";
        file_put_contents($path, $syntax);
    }

    /**
     * Delete schema folder from database directory
     *
     * @param string $database
     * @param string $tableName
     */
    public function deleteSchema(string $database, string $tableName): void
    {
        if ($database === "") {
            throw new EmptyStringException("database");
        }

        if ($tableName === "") {
            throw new EmptyStringException("tableName");
        }

        $path = $this->schemaFolderPath . "{$database}/{$tableName}.sql";
        if ($this->filesystem->exists($path)) {
            $this->filesystem->remove($path);
        }
    }

    /**
     * Create database folder in schema directory
     *
     * @param string $database
     */
    public function createDatabaseFolder(string $database): void
    {
        if ($database === "") {
            throw new EmptyStringException("database");
        }

        $path = $this->schemaFolderPath . $database;
        if (!$this->filesystem->exists($path)) {
            $this->filesystem->mkdir($path, self::DEFAULT_FOLDER_PERMISSIONS);
        }
    }

    /**
     * Delete database folder from schema directory
     *
     * @param string $database
     */
    public function deleteDatabaseFolder(string $database): void
    {
        if ($database === "") {
            throw new EmptyStringException($database);
        }

        $path = $this->schemaFolderPath . $database;
        if ($this->filesystem->exists($path)) {
            $this->filesystem->remove($path);
        }
    }
}
