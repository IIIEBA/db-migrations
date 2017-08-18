<?php

namespace DbMigrations\Module\Schema\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Status
 * @package DbMigrations\Command
 */
class Status extends AbstractSchemaCommand
{
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName("schema:status");
        $this->setDescription("Show tables status");
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
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->checkForceFlag($input);

        $this->getSchemaComponent()->showStatus(
            $input->getArgument("db-name"),
            $input->getArgument("schema-name")
        );
    }
}
