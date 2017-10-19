<?php

declare(strict_types=1);

namespace DbMigrations\Module\Migration\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Create
 * @package DbMigrations\Module\Migraion\Command
 */
class Create extends AbstractMigrationCommand
{
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName("migration:create");
        $this->setDescription("Create migration file");
        $this->addArgument(
            "db-name",
            InputArgument::REQUIRED,
            "Database name where need to run this migration",
            null
        );
        $this->addArgument(
            "migration-name",
            InputArgument::REQUIRED,
            "Name of migration (allowed only [a-zA-Z0-9_])",
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
