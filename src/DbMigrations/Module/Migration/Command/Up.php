<?php

declare(strict_types=1);

namespace DbMigrations\Module\Migration\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Up
 * @package DbMigrations\Module\Migraion\Command
 */
class Up extends AbstractMigrationCommand
{
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName("migration:up");
        $this->setDescription("Apply migrations to databases");
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
        $this->addOption(
            "only-single",
            "s",
            InputOption::VALUE_NONE,
            "Apply only requested migration"
        );
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

    }
}
