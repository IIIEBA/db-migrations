<?php

declare(strict_types=1);

namespace DbMigrations\Kernel\Command;

use DbMigrations\Kernel\Util\LoggerTrait;
use DbMigrations\Kernel\Util\StdInHelper;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class AbstractCommand
 * @package Kernel\Command
 */
class AbstractCommand extends Command
{
    use LoggerTrait;

    /**
     * @var StdInHelper
     */
    private $stdInHelper;

    /**
     * AbstractCommand constructor.
     *
     * @param StdInHelper $stdInHelper
     * @param LoggerInterface|null $logger
     */
    public function __construct(StdInHelper $stdInHelper, LoggerInterface $logger = null)
    {
        $this->stdInHelper = $stdInHelper;

        $this->setLogger($logger);

        parent::__construct();

        $this->addOption(
            "force-answer",
            "f",
            InputOption::VALUE_REQUIRED,
            "Force answer to all requested confirmations (y/Yes/n/No)",
            null
        );
    }

    /**
     * @return StdInHelper
     */
    public function getStdInHelper(): StdInHelper
    {
        return $this->stdInHelper;
    }

    /**
     * @param InputInterface $input
     */
    public function checkForceFlag(InputInterface $input)
    {
        $force = $input->getOption("force-answer");

        if ($force !== null) {
            switch ($force) {
                case "n":
                case "no":
                    $this->stdInHelper->setForceDecision(false);

                    break;

                case "y":
                case "yes":
                    $this->stdInHelper->setForceDecision(true);

                    break;
            }
        }
    }
}
