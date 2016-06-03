<?php

namespace DbMigrations\Command;

use DbMigrations\Model\MigrationStatus;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Migrate
 * @package DbMigrations\Command
 */
class Migrate extends AbstractCommand
{
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName("db-migrations:migrate");
        $this->setDescription("Migrate db from migration files");
        $this->addArgument(
            "name",
            InputArgument::OPTIONAL,
            "Migrate selected migration",
            null
        );
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $migrationsList = $this->getMigrationComponent()->getMigrationList(
            new MigrationStatus(MigrationStatus::NEW_ONE)
        );
    }
}
