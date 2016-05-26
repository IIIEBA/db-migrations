<?php

namespace DbMigrations\Command;

use BaseExceptions\Exception\InvalidArgument\EmptyStringException;
use BaseExceptions\Exception\InvalidArgument\NotStringException;
use DbMigrations\Component\Migration;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Console\Command\Command;

/**
 * Class AbstractCommand
 * @package DbMigrations\Command
 */
abstract class AbstractCommand extends Command
{
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
     * @var Migration
     */
    private $migrationComponent;

    /**
     * AbstractCommand constructor.
     * @param \PDO $pdo
     * @param string $schemaFolderPath
     * @param LoggerInterface|null $logger
     * @param string|null $name
     */
    public function __construct(
        \PDO $pdo,
        $schemaFolderPath,
        LoggerInterface $logger = null,
        $name = null
    ) {
        if (!is_string($schemaFolderPath)) {
            throw new NotStringException("schemaFolderPath");
        }
        if ($schemaFolderPath === "") {
            throw new EmptyStringException("schemaFolderPath");
        }

        $this->pdo = $pdo;
        $this->schemaFolderPath = $schemaFolderPath;
        $this->migrationComponent = new Migration($pdo, $schemaFolderPath, $logger);

        if (is_null($logger)) {
            $this->logger = new NullLogger();
        }
        
        // Set few params to PDO
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);

        parent::__construct($name);
    }

    /**
     * @return \PDO
     */
    public function getPdo()
    {
        return $this->pdo;
    }

    /**
     * @return string
     */
    public function getSchemaFolderPath()
    {
        return $this->schemaFolderPath;
    }

    /**
     * @return Migration
     */
    public function getMigrationComponent()
    {
        return $this->migrationComponent;
    }
    
    /**
     * @return LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * Get string witch user entered in terminal
     *
     * @param string|null $message
     * @return string
     */
    public function getStdIn($message = null)
    {
        if (!is_null($message) && is_string($message) && $message !== "") {
            echo "{$message}:\n";
        }
        
        $handle = fopen ("php://stdin","r");
        $line = trim(fgets($handle));
        fclose($handle);

        return $line;
    }
}
