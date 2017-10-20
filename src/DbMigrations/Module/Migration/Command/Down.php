<?php

declare(strict_types=1);

namespace DbMigrations\Module\Migration\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Down
 * @package DbMigrations\Module\Migraion\Command
 */
class Down extends AbstractMigrationCommand
{
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName("migration:down");
        $this->setDescription("Revert to or selected migration");
        $this->addArgument(
            "db-name",
            InputArgument::REQUIRED,
            "Database name where need to revert migration(s)",
            null
        );
        $this->addArgument(
            "migration-id",
            InputArgument::REQUIRED,
            "ID of migration",
            null
        );
        $this->addOption(
            "only-single",
            "s",
            InputOption::VALUE_NONE,
            "Revert only requested migration"
        );
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

    }
}
