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
            "schema- name",
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
            "re-init",
            null,
            InputOption::VALUE_NONE,
            "Re-init will remove existed tables and create new",
            null
        );
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->checkForceFlag($input);

        return;
    }
}
