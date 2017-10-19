<?php

declare(strict_types=1);

namespace DbMigrations\Module\Migration\Component;

use BaseExceptions\Exception\LogicException\NotImplementedException;
use DbMigrations\Kernel\Util\LoggerTrait;
use PDO;
use Psr\Log\LoggerInterface;

/**
 * Class AbstractMigration
 * @package DbMigrations\Module\Migration\Component
 */
class AbstractMigration implements MigrationInterface
{
    use LoggerTrait;

    const CLASS_NAME_REGEXP = "/Migration_(\d+)_([a-zA-Z0-9_]+)$/";

    /**
     * @var PDO
     */
    private $dbConnection;

    /**
     * AbstractMigration constructor.
     *
     * @param PDO $dbConnection
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        PDO $dbConnection,
        LoggerInterface $logger = null
    ) {
        $this->setLogger($logger);

        $this->dbConnection = $dbConnection;
    }

    /**
     * Magic for pre-run methods
     *
     * @param string $name
     * @param array $arguments
     * @throws \Exception
     * @throws \Throwable
     */
    public function __call(string $name, array $arguments)
    {
        if (!method_exists($this, $name)) {
            throw new \Exception("Method doesn't exist");
        }

        switch ($name) {
            case "up":
            case "down":
                $this->startTransaction();
                try {
                    call_user_func_array([$this, $name], $arguments);
                    $this->endTransaction();
                } catch (\Throwable | \Exception $exception) {
                    $this->endTransaction(true);
                    throw $exception;
                }

                break;

            default:
                call_user_func_array([$this, $name], $arguments);
        }
    }

    /**
     * Start transaction if not started yet
     */
    final public function startTransaction(): void
    {
        if ($this->getDbConnection()->inTransaction() === false) {
            $this->getDbConnection()->beginTransaction();
        }
    }

    /**
     * End transaction if not ended yet (commit on success and rollback on error)
     *
     * @param bool $onError
     */
    final public function endTransaction($onError = false): void
    {
        if ($this->getDbConnection()->inTransaction() === true) {
            $onError === false ? $this->getDbConnection()->commit() : $this->getDbConnection()->rollBack();
        }
    }

    /**
     * @return PDO
     */
    final public function getDbConnection(): PDO
    {
        return $this->dbConnection;
    }

    /**
     * Get parsed class name
     *
     * @return array
     */
    final private function getParsedClassName(): array
    {
        $className = get_called_class();

        preg_match(self::CLASS_NAME_REGEXP, $className, $matches);
        if (count($matches) !== 3) {
            throw new \InvalidArgumentException("Invalid class name was given - {$className}");
        }

        return $matches;
    }

    /**
     * @return string
     */
    final public function getId(): string
    {
        return $this->getParsedClassName()[1];
    }

    /**
     * @return string
     */
    final public function getName(): string
    {
        return $this->getParsedClassName()[2];
    }

    /**
     * Apply migration
     */
    public function up(): void
    {
        throw new NotImplementedException();
    }

    /**
     * Revert migration
     */
    public function down(): void
    {
        throw new NotImplementedException();
    }
}
