<?php

namespace DbMigrations\Module\Schema\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Migrate
 * @package DbMigrations\Command
 */
class Migrate extends AbstractSchemaCommand
{
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName("schema:migrate");
        $this->setDescription("Migrate db from migration files");
        $this->addArgument(
            "name",
            InputArgument::OPTIONAL,
            "Migrate selected migration",
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
