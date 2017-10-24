<?php

declare(strict_types=1);

namespace DbMigrations\Module\Migration\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
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
        $this->setName("{$this->getType()->getValue()}:create");
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
        $this->addOption(
            "schema-name",
            "s",
            InputOption::VALUE_OPTIONAL,
            "Create migration depends on schema file (only ALTER, not CREATE or DELETE)",
            null
        );
        $this->addOption(
            "is-heavy-migration",
            "p",
            InputOption::VALUE_NONE,
            "If heavy selected - migration will be applied by percona tools (IN PROGRESS)"
        );
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dbName = $input->getArgument("db-name");
        $migrationName = $this->getMigrationComponent()->createMigration(
            $dbName,
            $input->getArgument("migration-name"),
            $this->getType(),
            $input->getOption("is-heavy-migration"),
            $input->getOption("schema-name")
        );

        $output->writeln("New <comment>{$this->getType()->getValue()}</comment> migration in database"
            . " <comment>{$dbName}</comment> was successfully created with name - <comment>{$migrationName}</comment>");
        $output->writeln("");
    }
}
