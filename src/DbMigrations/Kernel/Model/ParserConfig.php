<?php

declare(strict_types=1);

namespace DbMigrations\Kernel\Model;

use BaseExceptions\Exception\InvalidArgument\EmptyStringException;
use BaseExceptions\Exception\InvalidArgument\NotStringException;
use DbMigrations\Kernel\Enum\ParserType;

/**
 * Class ParserConfig
 * @package Kernel\Model
 */
class ParserConfig implements ParserConfigInterface
{
    /**
     * @var ParserType
     */
    private $type;
    /**
     * @var string
     */
    private $name;
    /**
     * @var string
     */
    private $folderPath;
    /**
     * @var array
     */
    private $filesList;
    /**
     * @var array
     */
    private $subVariablesList;

    /**
     * ParserConfig constructor.
     * @param ParserType $type
     * @param string $name
     * @param string $folderPath
     * @param array $filesList
     * @param array $subVariablesList
     */
    public function __construct(
        ParserType $type,
        string $name,
        string $folderPath,
        array $filesList,
        array $subVariablesList
    ) {
        array_walk($filesList, function ($file) {
            if (!is_string($file)) {
                throw new NotStringException("file");
            }
            if ($file === "") {
                throw new EmptyStringException("file");
            }
        });

        array_walk($subVariablesList, function ($subVariable) {
            if (!is_string($subVariable)) {
                throw new NotStringException("subVariable");
            }
            if ($subVariable === "") {
                throw new EmptyStringException("subVariable");
            }
        });

        $this->type = $type;
        $this->name = $name;
        $this->folderPath = rtrim($folderPath, "/") . "/";
        $this->filesList = $filesList;
        $this->subVariablesList = $subVariablesList;
    }

    /**
     * @return ParserType
     */
    public function getType(): ParserType
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getFolderPath(): string
    {
        return $this->folderPath;
    }

    /**
     * @return array
     */
    public function getFilesList(): array
    {
        return $this->filesList;
    }

    /**
     * @return array
     */
    public function getSubVariablesList(): array
    {
        return $this->subVariablesList;
    }
}
