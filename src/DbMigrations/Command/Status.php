<?php

namespace DbMigrations\Command;

use DbMigrations\Model\TableChangesAction;
use DbMigrations\Model\TableInfoInterface;
use DbMigrations\Model\TableInfoStatus;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
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
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("<comment>Trying to check tables status:</comment>" . PHP_EOL);
        $tableList = $this->getMigrationComponent()->tablesStatus($input->getArgument("name"));

        if (empty($tableList)) {
            $output->writeln("    No tables found");
            return;
        }

        foreach ($tableList as $table) {
            $this->showTableName($table, $output);
            $this->showModifiedFields($table, $output);
            $this->showCreateTableSyntax($table, $output);
        }
    }

    /**
     * Show information about table status
     *
     * @param TableInfoInterface $table
     * @param OutputInterface $output
     */
    public function showTableName(TableInfoInterface $table, OutputInterface $output)
    {
        switch ($table->getStatus()->getValue()) {
            case TableInfoStatus::MODIFIED:
                $prefix = "<comment>  ? ";
                $suffix = "</comment>";
                break;

            case TableInfoStatus::CREATED:
                $prefix = "<info>  + ";
                $suffix = "</info>";
                break;

            case TableInfoStatus::REMOVED:
                $prefix = "<fg=red>  - ";
                $suffix = "</>";
                break;

            default:
                $prefix = "    ";
                $suffix = "";
        }
        $output->writeln(
            $prefix . "Table `" . $table->getTableName() . "` is " . $table->getStatus()->getValue() . $suffix
        );
    }

    /**
     * Show information about table modifications
     *
     * @param TableInfoInterface $table
     * @param OutputInterface $output
     */
    public function showModifiedFields(TableInfoInterface $table, OutputInterface $output)
    {
        if ($output->isVerbose() && $table->getStatus()->isEquals(TableInfoStatus::MODIFIED)) {
            foreach ($table->getChanges() as $changes) {
                switch ($changes->getAction()->getValue()) {
                    case TableChangesAction::ADD:
                        $prefix = "<info>  + ";
                        $suffix = "</info>";
                        break;

                    case TableChangesAction::REMOVE:
                        $prefix = "<fg=red>  - ";
                        $suffix = "</>";
                        break;

                    default:
                        $prefix = "    ";
                        $suffix = "";
                }

                $output->writeln("    " . $prefix . $changes->getField(). $suffix);
            }
        }
    }

    /**
     * Show create table syntax
     *
     * @param TableInfoInterface $table
     * @param OutputInterface $output
     */
    public function showCreateTableSyntax(TableInfoInterface $table, OutputInterface $output)
    {
        if ($output->isVeryVerbose()) {
            $prefix = "        ";
            $createSyntax = [
                "Create table from schema syntax:" => $table->getSchemaSyntax(),
                "Create table from db syntax:" => $table->getDbSyntax(),
            ];

            foreach ($createSyntax as $name => $syntax) {
                if (is_null($syntax)) {
                    continue;
                }

                $output->writeln($prefix . "<fg=cyan>" . $name . "</>");
                foreach (explode("\n", $syntax) as $line) {
                    $output->writeln($prefix . "    " . $line);
                }
            }
        }
    }
}
