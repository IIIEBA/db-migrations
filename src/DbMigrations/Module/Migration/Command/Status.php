<?php

declare(strict_types=1);

namespace DbMigrations\Module\Migration\Command;

use DbMigrations\Module\Migration\Enum\MigrationStatusType;
use DbMigrations\Module\Migration\Enum\MigrationType;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Status
 * @package DbMigrations\Module\Migraion\Command
 */
class Status extends AbstractMigrationCommand
{
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName("structure:status");
        $this->setDescription("Show status for migrations");
        $this->addArgument(
            "db-name",
            InputArgument::OPTIONAL,
            "Database name where need to apply migration(s)",
            null
        );
        $this->addArgument(
            "migration-id",
            InputArgument::OPTIONAL,
            "ID of migration",
            null
        );
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $status = $this->getMigrationComponent()->migrationsStatus(
            new MigrationType(MigrationType::STRUCTURE),
            $input->getArgument("db-name"),
            $input->getArgument("migration-id")
        );

        foreach ($status as $db) {
            $output->writeln(
                "<bg=white;fg=black> --- Database '{$db->getName()}' --- </>"
            );

            $table = new Table($output);
            $table->setHeaders([
                "Migration ID",
                "Name",
                "Status",
                "Started At",
                "Applied At",
            ]);

            foreach ($db->getMigrationStatusList() as $migration) {
                $table->addRow([
                    $migration->getMigrationId(),
                    $migration->getName(),
                    ($migration->getType()->isEquals(MigrationStatusType::NEW) ? "<fg=red>" : "<fg=green>")
                        . (strtoupper($migration->getType()->getValue()) . "</>"),
                    $migration->getType()->isEquals(MigrationStatusType::APPLIED)
                        ? date("Y-m-d H:i:s", intval($migration->getStartedAt())) : "-",
                    $migration->getType()->isEquals(MigrationStatusType::APPLIED)
                        ? date("Y-m-d H:i:s", intval($migration->getAppliedAt())) : "-",
                ]);
            }

            $table->render();
            $output->writeln("");
        }
    }
}
