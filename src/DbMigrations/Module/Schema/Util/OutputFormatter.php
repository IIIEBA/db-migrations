<?php

declare(strict_types=1);

namespace DbMigrations\Module\Schema\Util;

use DbMigrations\Kernel\Util\LoggerTrait;
use DbMigrations\Kernel\Util\StdInHelper;
use DbMigrations\Module\Schema\Enum\DbInfoStatus;
use DbMigrations\Module\Schema\Enum\TableChangesAction;
use DbMigrations\Module\Schema\Model\TableInfoInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class OutputFormatter
 * @package DbMigrations\Module\Schema\Util
 */
class OutputFormatter
{
    use LoggerTrait;
    /**
     * @var OutputInterface
     */
    private $output;
    /**
     * @var StdInHelper
     */
    private $stdInHelper;

    /**
     * OutputFormatter constructor.
     *
     * @param OutputInterface $output
     * @param StdInHelper $stdInHelper
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        OutputInterface $output,
        StdInHelper $stdInHelper,
        LoggerInterface $logger = null
    ) {
        $this->output = $output;
        $this->stdInHelper = $stdInHelper;

        $this->setLogger($logger);
    }


    /**
     * Show information about table status
     *
     * @param TableInfoInterface $table
     */
    public function showTableName(TableInfoInterface $table)
    {
        switch ($table->getStatus()->getValue()) {
            case DbInfoStatus::MODIFIED:
                $prefix = "<comment>  {$table->getStatus()->getValue()} ";
                $suffix = "</comment>";
                break;

            case DbInfoStatus::CREATED:
                $prefix = "<info>  {$table->getStatus()->getValue()} ";
                $suffix = "</info>";
                break;

            case DbInfoStatus::REMOVED:
                $prefix = "<fg=red>  {$table->getStatus()->getValue()} ";
                $suffix = "</>";
                break;

            default:
                $prefix = "    ";
                $suffix = "";
        }
        $this->output->writeln(
            ($this->output->isVerbose() ? PHP_EOL : "")
            . $prefix . "Table `" . $table->getTableName() . "` is " . $table->getStatus()->getValue() . $suffix
        );
    }

    /**
     * Show information about table modifications
     *
     * @param TableInfoInterface $table
     */
    public function showModifiedFields(TableInfoInterface $table)
    {
        if ($this->output->isVerbose() && $table->getStatus()->isEquals(DbInfoStatus::MODIFIED)) {
            foreach ($table->getChanges() as $changes) {
                switch ($changes->getAction()->getValue()) {
                    case TableChangesAction::ADD:
                        $prefix = "<info>  {$changes->getAction()->getValue()} ";
                        $suffix = "</info>";
                        break;

                    case TableChangesAction::REMOVE:
                        $prefix = "<fg=red>  {$changes->getAction()->getValue()} ";
                        $suffix = "</>";
                        break;

                    case TableChangesAction::MODIFIED:
                        $prefix = "<comment>  {$changes->getAction()->getValue()} ";
                        $suffix = "</comment>";
                        break;

                    default:
                        $prefix = "    ";
                        $suffix = "";
                }

                $this->output->writeln("    " . $prefix . $changes->getField(). $suffix);
            }
        }
    }

    /**
     * Show create table syntax
     *
     * @param TableInfoInterface $table
     */
    public function showCreateTableSyntax(TableInfoInterface $table)
    {
        if ($this->output->isVeryVerbose()) {
            $prefix = "        ";

            if ($table->getStatus()->isEquals(DbInfoStatus::ACTUAL)) {
                $createSyntax = [
                    "Create table syntax:" => $table->getSchemaSyntax(),
                ];
            } else {
                $createSyntax = [
                    "Create table from schema syntax:" => $table->getSchemaSyntax(),
                    "Create table from db syntax:" => $table->getDbSyntax(),
                ];
            }

            foreach ($createSyntax as $name => $syntax) {
                if (is_null($syntax)) {
                    continue;
                }

                $this->output->writeln($prefix . "<fg=cyan>" . $name . "</>");
                foreach (explode("\n", $syntax) as $line) {
                    $this->output->writeln($prefix . "    " . $line);
                }
            }
        }
    }
}
