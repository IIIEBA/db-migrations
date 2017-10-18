<?php

declare(strict_types=1);

namespace DbMigrations\Module\Schema\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Dump
 * @package DbMigrations\Module\Schema\Command
 */
class Dump extends AbstractSchemaCommand
{
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName("schema:dump");
        $this->setDescription("Create database dump to schema files");
        $this->addArgument(
            "db-name",
            InputArgument::OPTIONAL,
            "Dump only one db",
            null
        );
        $this->addArgument(
            "table-name",
            InputArgument::OPTIONAL,
            "Dump only one table",
            null
        );
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->checkForceFlag($input);

        $this->getSchemaComponent()->dumpDb(
            $input->getArgument("db-name"),
            $input->getArgument("table-name")
        );
    }
}
