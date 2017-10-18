<?php

declare(strict_types=1);

namespace DbMigrations\Kernel\Component\Parser;

/**
 * Class ParserInterface
 * @package Kernel\Component\Parser
 */
interface ParserInterface
{
    /**
     * @param string $name
     * @return mixed
     */
    public function get(string $name);
}
