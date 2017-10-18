<?php

declare(strict_types=1);

namespace DbMigrations\Kernel\Model;

use DbMigrations\Kernel\Enum\ParserType;

/**
 * Class ParserConfig
 * @package Kernel\Model
 */
interface ParserConfigInterface
{
    /**
     * @return ParserType
     */
    public function getType(): ParserType;

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return string
     */
    public function getFolderPath(): string;

    /**
     * @return array
     */
    public function getFilesList(): array;

    /**
     * @return array
     */
    public function getSubVariablesList(): array;
}
