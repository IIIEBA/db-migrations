<?php

declare(strict_types=1);

namespace DbMigrations\Module\Migration\Command;

use DbMigrations\Kernel\Command\AbstractCommand;
use DbMigrations\Kernel\Util\StdInHelper;
use DbMigrations\Module\MIgration\Component\MigrationComponentInterface;
use DbMigrations\Module\Migration\Enum\MigrationType;
use Psr\Log\LoggerInterface;

/**
 * Class AbstractMigrationCommand
 * @package DbMigrations\Module\Migration\Command
 */
class AbstractMigrationCommand extends AbstractCommand
{
    /**
     * @var MigrationComponentInterface
     */
    private $migrationComponent;
    /**
     * @var MigrationType
     */
    private $type;

    /**
     * AbstractSchemaCommand constructor.
     *
     * @param MigrationComponentInterface $migrationComponent
     * @param StdInHelper $stdInHelper
     * @param MigrationType $type
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        MigrationComponentInterface $migrationComponent,
        StdInHelper $stdInHelper,
        MigrationType $type,
        LoggerInterface $logger = null
    ) {
        $this->migrationComponent = $migrationComponent;
        $this->type = $type;

        parent::__construct($stdInHelper, $logger);
    }

    /**
     * @return MigrationComponentInterface
     */
    public function getMigrationComponent(): MigrationComponentInterface
    {
        return $this->migrationComponent;
    }

    /**
     * @return MigrationType
     */
    public function getType(): MigrationType
    {
        return $this->type;
    }
}
