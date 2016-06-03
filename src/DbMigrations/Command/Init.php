<?php

namespace DbMigrations\Command;

use DbMigrations\Model\InitTableStatus;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Init
 * @package DbMigrations\Command
 */
class Init extends AbstractCommand
{
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName("db-migrations:init-schema");
        $this->setDescription("Init db from schema files");
        $this->addArgument(
            "name",
            InputArgument::OPTIONAL,
            "Init only one schema file",
            null
        );
        $this->addOption(
            "without-data",
            null,
            InputOption::VALUE_NONE,
            "Skip init data from 'init' folder",
            null
        );
        $this->addOption(
            "skip-exists",
            null,
            InputOption::VALUE_NONE,
            "Skip existed tables",
            null
        );
        $this->addOption(
            "force",
            null,
            InputOption::VALUE_NONE,
            "Force init will remove existed tables and create new",
            null
        );
        $this->addOption(
            "schema-folder",
            null,
            InputOption::VALUE_OPTIONAL,
            "Custom path to schema folder",
            null
        );
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $migrationsList = $this->getMigrationComponent()->initDb(
            boolval($input->getOption("without-data")),
            boolval($input->getOption("skip-exists")),
            boolval($input->getOption("force")),
            $input->getArgument("name"),
            $input->getOption("schema-folder")
        );

        foreach ($migrationsList->getResult() as $name => $result) {
            $output->writeln("<info>$name - " . $result->getStatus()->getValue() . "</info>");
            if ($output->isVerbose() && $result->getStatus()->isEquals(InitTableStatus::ERROR)) {
                $output->writeln("<error>" . $result->getDesc() . "</error>");
            }
        }
    }
}
