<?php

declare(strict_types=1);

namespace DbMigrations\Module\Migration\Component;

use BaseExceptions\Exception\InvalidArgument\EmptyStringException;
use DbMigrations\Kernel\Component\DbConnectionInterface;
use DbMigrations\Kernel\Util\LoggerTrait;
use DbMigrations\Module\Migration\Dao\MigrationStatusRepository;
use DbMigrations\Module\Migration\Dao\MigrationStatusRepositoryInterface;
use DbMigrations\Module\Migration\Enum\MigrationType;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class MigrationRepositoryManager
 * @package DbMigrations\Module\Migration\Component
 */
class MigrationRepositoryManager implements MigrationRepositoryManagerInterface
{
    use LoggerTrait;

    /**
     * @var DbConnectionInterface
     */
    private $dbConnection;
    /**
     * @var MigrationStatusRepositoryInterface[] #mapped by string key
     */
    private $repositoryList = [];
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * MigrationRepositoryManager constructor.
     *
     * @param DbConnectionInterface $dbConnection
     * @param Filesystem $filesystem
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        DbConnectionInterface $dbConnection,
        Filesystem $filesystem,
        LoggerInterface $logger = null
    ) {
        $this->setLogger($logger);

        $this->dbConnection = $dbConnection;
        $this->filesystem = $filesystem;
    }

    /**
     * Get existed or generate new migration repository for each variant of params
     *
     * @param string $dbName
     * @param MigrationType $type
     * @return MigrationStatusRepositoryInterface
     */
    public function get(string $dbName, MigrationType $type): MigrationStatusRepositoryInterface
    {
        if ($dbName === "") {
            throw new EmptyStringException("dbName");
        }

        $key = "{$dbName}_{$type->getValue()}";
        if (array_key_exists($key, $this->repositoryList) === false) {
            $this->repositoryList[$key] = new MigrationStatusRepository(
                $this->dbConnection->getConnection($dbName),
                $this->filesystem,
                $type,
                $this->getLogger()
            );
        }

        return $this->repositoryList[$key];
    }
}
