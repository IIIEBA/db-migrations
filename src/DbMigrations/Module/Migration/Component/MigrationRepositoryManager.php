<?php

declare(strict_types=1);

namespace DbMigrations\Module\Migration\Component;

use BaseExceptions\Exception\InvalidArgument\EmptyStringException;
use DbMigrations\Kernel\Component\DbConnectionInterface;
use DbMigrations\Kernel\Util\LoggerTrait;
use DbMigrations\Module\Migration\Dao\MigrationStatusRepository;
use DbMigrations\Module\Migration\Dao\MigrationStatusRepositoryInterface;
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
     * @var string
     */
    private $type;

    /**
     * MigrationRepositoryManager constructor.
     *
     * @param DbConnectionInterface $dbConnection
     * @param Filesystem $filesystem
     * @param string $type
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        DbConnectionInterface $dbConnection,
        Filesystem $filesystem,
        string $type,
        LoggerInterface $logger = null
    ) {
        $this->setLogger($logger);

        $this->dbConnection = $dbConnection;
        $this->filesystem = $filesystem;
        $this->type = $type;
    }

    /**
     * Get existed or generate new migration repository for each variant of params
     *
     * @param string $dbName
     * @return MigrationStatusRepositoryInterface
     */
    public function get(string $dbName): MigrationStatusRepositoryInterface
    {
        if ($dbName === "") {
            throw new EmptyStringException("dbName");
        }

        if (array_key_exists($dbName, $this->repositoryList) === false) {
            $this->repositoryList[$dbName] = new MigrationStatusRepository(
                $this->dbConnection->getConnection($dbName),
                $this->filesystem,
                $this->type,
                $this->getLogger()
            );
        }

        return $this->repositoryList[$dbName];
    }
}
