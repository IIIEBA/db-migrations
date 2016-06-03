<?php

namespace DbMigrations\Component;

use BaseExceptions\Exception\InvalidArgument\EmptyStringException;
use BaseExceptions\Exception\InvalidArgument\NotStringException;
use DbMigrations\Model\MigrationInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class MigrationBuilder
 * @package DbMigrations\Component
 */
class MigrationBuilder
{
    /**
     * @var string
     */
    private $migrationsFolderPath;
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * MigrationBuilder constructor.
     * @param \PDO $pdo
     * @param string $migrationsFolderPath
     * @param LoggerInterface $logger
     */
    public function __construct(
        \PDO $pdo,
        $migrationsFolderPath,
        LoggerInterface $logger = null
    ) {
        if (!is_string($migrationsFolderPath)) {
            throw new NotStringException("migrationsFolderPath");
        }
        if ($migrationsFolderPath === "") {
            throw new EmptyStringException("migrationsFolderPath");
        }

        $this->pdo = $pdo;
        $this->migrationsFolderPath = $migrationsFolderPath;

        if (is_null($logger)) {
            $this->logger = new NullLogger();
        }

        $this->filesystem = new Filesystem();
    }

    /**
     * Return new instance of migration class
     * 
     * @param MigrationInterface $migration
     * @return MigrationCommandInterface
     */
    public function buildMigration(MigrationInterface $migration)
    {
        $migrationPath = $this->migrationsFolderPath . "/" . $migration->getName() . ".php";
        if ($this->filesystem->exists($migrationPath) === false) {
            throw new \LogicException("Migration " . $migration->getName() . "not exists");
        }

        // Init class
        require_once $migrationPath;
        $className = $migration->getClassName();
        $migrationClass = new $className($this->pdo, $this->logger);

        if ($migrationClass instanceof MigrationCommandInterface === false) {
            throw new \LogicException(
                "Migration " . $migration->getName() . " must be instance of MigrationCommandInterface"
            );
        }
        
        return $migrationClass;
    }
}
