<?php

namespace DbMigrations\Module\Schema\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Init
 * @package DbMigrations\Command
 */
class Init extends AbstractSchemaCommand
{
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName("schema:init");
        $this->setDescription("Init databases from schema files");
        $this->addArgument(
            "db-name",
            InputArgument::OPTIONAL,
            "Init only one db folder",
            null
        );
        $this->addArgument(
            "schema-name",
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
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->checkForceFlag($input);

        $this->getSchemaComponent()->initDb(
            $input->getArgument("db-name"),
            $input->getArgument("schema-name"),
            $input->getOption("without-data")
        );
    }
}
