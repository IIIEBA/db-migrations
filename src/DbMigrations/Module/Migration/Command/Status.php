<?php

declare(strict_types=1);

namespace DbMigrations\Module\Migration\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Status
 * @package DbMigrations\Module\Migraion\Command
 */
class Status extends AbstractMigrationCommand
{
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName("structure:status");
        $this->setDescription("Show status for migrations");
        $this->addArgument(
            "db-name",
            InputArgument::OPTIONAL,
            "Database name where need to apply migration(s)",
            null
        );
        $this->addArgument(
            "migration-id",
            InputArgument::OPTIONAL,
            "ID of migration",
            null
        );
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

    }
}
