<?php

declare(strict_types=1);

namespace DbMigrations\Kernel\Component\Parser;

use DbMigrations\Kernel\Exception\GeneralException;
use DbMigrations\Kernel\Model\ParserConfigInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

/**
 * Class YamlParser
 * @package Kernel\Component\Parser
 */
class YamlParser extends AbstractParser implements ParserInterface
{
    /**
     * @var Yaml
     */
    private $yaml;
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * YamlParser constructor.
     * @param Yaml $yaml
     * @param Filesystem $filesystem
     * @param ParserConfigInterface $config
     * @throws GeneralException
     */
    public function __construct(
        Yaml $yaml,
        Filesystem $filesystem,
        ParserConfigInterface $config
    ) {
        $this->yaml = $yaml;
        $this->filesystem = $filesystem;

        // Load files
        $params = [];
        foreach ($config->getFilesList() as $filename) {
            $filePath = $config->getFolderPath() . $filename;
            if ($this->filesystem->exists($filePath) === false) {
                throw new GeneralException("Config file does not exists - `{$filePath}`");
            }

            $params = array_replace_recursive($params, $this->yaml->parse(file_get_contents($filePath)));
        }

        // Selecting sub variables
        foreach ($config->getSubVariablesList() as $name) {
            if (!array_key_exists($name, $params)) {
                throw new GeneralException("Variable does not exists in config file - `{$name}`");
            }

            $params = $params[$name];
        }

        $this->params = $params;
    }
}
