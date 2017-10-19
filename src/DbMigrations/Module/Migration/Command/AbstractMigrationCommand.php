<?php

declare(strict_types=1);

namespace DbMigrations\Module\Migration\Command;

use DbMigrations\Kernel\Command\AbstractCommand;
use DbMigrations\Kernel\Util\StdInHelper;
use DbMigrations\Module\MIgration\Component\MigrationComponentInterface;
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
     * AbstractSchemaCommand constructor.
     *
     * @param MigrationComponentInterface $migrationComponent
     * @param StdInHelper $stdInHelper
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        MigrationComponentInterface $migrationComponent,
        StdInHelper $stdInHelper,
        LoggerInterface $logger = null
    ) {
        $this->migrationComponent = $migrationComponent;

        parent::__construct($stdInHelper, $logger);
    }

    /**
     * @return MigrationComponentInterface
     */
    public function getMigrationComponent(): MigrationComponentInterface
    {
        return $this->migrationComponent;
    }
}
