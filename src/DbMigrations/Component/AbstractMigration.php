<?php

namespace DbMigrations\Component;

use BaseExceptions\Exception\LogicException\NotImplementedException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Class AbstractMigration
 * @package DbMigrations\Component
 */
class AbstractMigration
{
    /**
     * @var \PDO
     */
    private $pdo;
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * AbstractMigration constructor.
     * @param \PDO $pdo
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        \PDO $pdo,
        LoggerInterface $logger = null
    ) {
        $this->pdo = $pdo;

        if (is_null($logger)) {
            $this->logger = new NullLogger();
        }

        // Set few params to PDO
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
    }

    /**
     * @return \PDO
     */
    public function getPdo()
    {
        return $this->pdo;
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * Run migration
     */
    public function up()
    {
        throw new NotImplementedException();
    }

    /**
     * Rollback migration
     */
    public function down()
    {
        throw new NotImplementedException();
    }

    /**
     * Run migration in transaction
     */
    public function safeUp()
    {
        throw new NotImplementedException();
    }

    /**
     * Rollback migration in transaction
     */
    public function safeDown()
    {
        throw new NotImplementedException();
    }
}
