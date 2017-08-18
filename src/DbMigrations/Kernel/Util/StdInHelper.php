<?php

declare(strict_types=1);

namespace DbMigrations\Kernel\Util;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class StdInHelper
 * @package Kernel\Util
 */
class StdInHelper
{
    /**
     * @var null|bool
     */
    private $force;
    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * StdInHelper constructor.
     *
     * @param OutputInterface $output
     */
    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * Set force answer for all confirm requests
     *
     * @param bool $status
     */
    public function setForceDecision(bool $status): void
    {
        $this->force = $status;
    }

    /**
     * Get input
     *
     * @param string|null $message
     * @return string
     */
    public function getInput(string $message = null): string
    {
        return $this->getStdIn($message);
    }

    /**
     * Get confirmation result
     *
     * @param string|null $message
     * @param bool $default
     * @return bool
     */
    public function confirm(string $message = null, bool $default = false): bool
    {
        // Check fo force result
        if ($this->force !== null) {
            return $this->force;
        }

        $defaultMsg = $default ? "Y" : "N";
        $message .= (!empty($message) ? " " : "") . "(Enter Y/Yes/N/No, default answer is `{$defaultMsg}`)";

        $answer = $this->getStdIn($message);
        return empty($answer) ? $default : in_array(strtolower($answer), ["y", "yes"]);
    }

    /**
     * Output message to console and get entered text
     *
     * @param string|null $message
     * @return string
     */
    private function getStdIn(string $message = null): string
    {
        if (!is_null($message) && $message !== "") {
            $this->output->writeln("<comment>{$message}:</comment>");
        }

        $handle = fopen("php://stdin", "r");
        $line = trim(fgets($handle));
        fclose($handle);

        return $line;
    }
}
