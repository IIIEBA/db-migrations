<?php

namespace Lib\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Create
 * @package Lib\Command
 */
class Create extends AbstractCommand
{
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName("db-migrations:create");
        $this->setDescription("Create new migration file");
        $this->addArgument("name", InputArgument::REQUIRED, "Name of new migration file");
        $this->addOption(
            "without-transaction",
            null,
            InputOption::VALUE_NONE,
            "Run migration without transaction",
            null
        );
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $this->getMigrationComponent()->createMigration(
            $input->getArgument("name"),
            boolval($input->getOption("without-transaction"))
        );

        $output->writeln("<info>New transaction was successful created with name - {$name}</info>");
        $output->writeln("");
    }
}
