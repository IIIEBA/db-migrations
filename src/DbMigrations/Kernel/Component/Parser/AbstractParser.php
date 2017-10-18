<?php

declare(strict_types=1);

namespace DbMigrations\Kernel\Component\Parser;

use DbMigrations\Kernel\Exception\GeneralException;

/**
 * Class AbstractParser
 * @package Kernel\Component\Parser
 */
abstract class AbstractParser
{
    /**
     * @var array
     */
    protected $params;

    /**
     * @param string $name
     * @return mixed
     * @throws GeneralException
     */
    public function get(string $name)
    {
        if (!array_key_exists($name, $this->params)) {
            throw new GeneralException("Trying to get unregistered param - `{$name}`");
        }

        return $this->params[$name];
    }
}
