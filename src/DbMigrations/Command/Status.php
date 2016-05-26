<?php

namespace DbMigrations\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Status
 * @package DbMigrations\Command
 */
class Status extends AbstractCommand
{
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName("db-migrations:status");
        $this->setDescription("Show tables status");
        $this->addArgument(
            "name",
            InputArgument::OPTIONAL,
            "Check only selected table",
            null
        );
        $this->addOption(
            "diff",
            null,
            InputOption::VALUE_NONE,
            "Show tables diff (bd vs file)",
            null
        );
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getMigrationComponent()->tablesStatus(
            $input->getArgument("name"),
            boolval($input->getOption("diff"))
        );
    }
}
