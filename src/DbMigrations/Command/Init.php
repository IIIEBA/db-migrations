<?php

namespace DbMigrations\Command;

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
        $this->setName("db-migrations:init");
        $this->setDescription("Init db from schema files");
        $this->addOption(
            "with-data",
            null,
            InputOption::VALUE_NONE,
            "Add init data from 'init' folder",
            null
        );
        $this->addOption(
            "init-folder",
            null,
            InputOption::VALUE_OPTIONAL,
            "Custom path to list of init data SQL files",
            null
        );
        $this->addOption(
            "force",
            null,
            InputOption::VALUE_NONE,
            "Force init will remove existed tables and create new",
            null
        );
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        var_dump($input->getOption("with-data"));
    }
}
