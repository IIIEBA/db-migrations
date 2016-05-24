<?php

namespace Lib\Command;

use Lib\Component\Migration;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Console\Command\Command;

/**
 * Class AbstractCommand
 * @package Lib\Command
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
        $logger = null,
        $name = null
    ) {
        $this->pdo = $pdo;
        $this->schemaFolderPath = $schemaFolderPath;
        $this->migrationComponent = new Migration($pdo, $schemaFolderPath, $logger);

        if (is_null($logger)) {
            $this->logger = new NullLogger();
        }
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
