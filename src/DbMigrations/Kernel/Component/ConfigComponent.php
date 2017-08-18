<?php

declare(strict_types=1);

namespace DbMigrations\Kernel\Component;

use BaseExceptions\Exception\InvalidArgument\EmptyArrayException;
use BaseExceptions\Exception\InvalidArgument\EmptyStringException;
use BaseExceptions\Exception\InvalidArgument\NotArrayException;
use BaseExceptions\Exception\InvalidArgument\NotPositiveNumericException;
use BaseExceptions\Exception\InvalidArgument\NotStringException;
use DbMigrations\Kernel\Component\Parser\ParserInterface;
use DbMigrations\Kernel\Component\Parser\YamlParser;
use DbMigrations\Kernel\Enum\ParserType;
use DbMigrations\Kernel\Exception\GeneralException;
use DbMigrations\Kernel\Model\GeneralConfig;
use DbMigrations\Kernel\Model\GeneralConfigInterface;
use DbMigrations\Kernel\Model\ParserConfig;
use DbMigrations\Kernel\Model\ParserConfigInterface;
use DbMigrations\Kernel\Util\LoggerTrait;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

/**
 * Class ConfigComponent
 * @package Kernel
 */
class ConfigComponent
{
    use LoggerTrait;

    /**
     * @var mixed[name]
     */
    private $environmentList = [];
    /**
     * @var GeneralConfigInterface
     */
    private $generalConfig;
    /**
     * @var ParserInterface[]
     */
    private $parserList = [];
    /**
     * @var Filesystem
     */
    private $filesystem;
    /**
     * @var Yaml
     */
    private $yaml;
    /**
     * @var OutputInterface
     */
    private $output;
    /**
     * @var DbConnection
     */
    private $dbConnection;

    /**
     * ConfigComponent constructor.
     * @param string $configPath
     * @param Filesystem $filesystem
     * @param Yaml $yaml
     * @param DbConnection $dbConnection
     * @param OutputInterface $output
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        $configPath,
        Filesystem $filesystem,
        Yaml $yaml,
        DbConnection $dbConnection,
        OutputInterface $output,
        LoggerInterface $logger = null
    ) {
        $this->filesystem = $filesystem;
        $this->yaml = $yaml;
        $this->output = $output;
        $this->dbConnection = $dbConnection;

        $this->setLogger($logger);

        $this->parsePlaintConfig($yaml::parse(file_get_contents($configPath)));
    }

    /**
     * Parse all config blocks
     *
     * @param array $config
     * @throws GeneralException
     */
    public function parsePlaintConfig(array $config)
    {
        if (!array_key_exists("setup", $config)) {
            throw new GeneralException("Setup block is mission in config file");
        }
        $setup = $config["setup"];

        // Parse environment block
        if (array_key_exists("environment", $setup)) {
            if (!is_array($setup["environment"]) && !is_null($setup["environment"])) {
                throw new NotArrayException("config->environment");
            }
            if (!empty($setup["environment"])) {
                $this->parseEnvironmentBLock($setup["environment"]);
            }
        }

        // Parse parser block
        if (array_key_exists("parsers", $setup)) {
            if (!is_array($setup["parsers"]) && !is_null($setup["parsers"])) {
                throw new NotArrayException("config->parsers");
            }
            if (!empty($setup["parsers"])) {
                $this->parseParsersBlock($setup["parsers"]);
            }
        }

        // Parse general block
        if (!array_key_exists("general", $setup)) {
            throw new GeneralException("General block is missing in config file");
        }
        if (!is_array($setup["general"])) {
            throw new NotArrayException("config->general");
        }
        if (empty($setup["general"])) {
            throw new EmptyArrayException("config->general");
        }
        $this->parseGeneralBlock($setup["general"]);

        // Parse db block
        if (!array_key_exists("db", $config)) {
            throw new GeneralException("General block is missing in config file");
        }
        if (!is_array($config["db"])) {
            throw new NotArrayException("config->db");
        }
        if (empty($config["db"])) {
            throw new EmptyArrayException("config->db");
        }
        $this->parseDbBlock($config["db"]);
    }

    /**
     * Parse environment config block
     *
     * @param array $config
     * @throws GeneralException
     */
    public function parseEnvironmentBLock(array $config)
    {
        foreach ($config as $env => $params) {
            // Check env name
            if (!is_string($env)) {
                throw new NotStringException("env");
            }

            // Check params
            if (!array_key_exists("system", $params) || !array_key_exists("default", $params)) {
                throw new GeneralException("Invalid params was given for env - `{$env}`");
            }

            $systemValue = getenv($params["system"]);
            $this->setEnvironment($env, $systemValue !== false ? $systemValue : $params["default"]);
        }
    }

    /**
     * @param string $name
     * @param mixed $value
     * @throws GeneralException
     */
    public function setEnvironment($name, $value)
    {
        if (!is_string($name)) {
            throw new NotStringException("name");
        }
        if ($name === "") {
            throw new EmptyStringException("name");
        }

        if (!is_scalar($value)) {
            throw new \InvalidArgumentException("Only scalar available for `value`");
        }

        if (array_key_exists($name, $this->environmentList)) {
            throw new GeneralException("This environment is already exists - `{$name}`");
        }

        $this->environmentList[$name] = $value;
    }

    /**
     * Get parsed env value
     *
     * @param string $name
     * @return mixed
     * @throws GeneralException
     */
    public function getEnvironment($name)
    {
        if (!array_key_exists($name, $this->environmentList)) {
            throw new GeneralException("Trying to get unregistered environment - `{$name}`");
        }

        return $this->environmentList[$name];
    }

    /**
     * Parse general config block
     *
     * @param array $config
     * @throws GeneralException
     */
    public function parseGeneralBlock(array $config)
    {
        if (!array_key_exists("dbFolderPath", $config)) {
            throw new GeneralException("Invalid format for general config, `dbFolderPath` is missing");
        }

        $this->generalConfig = new GeneralConfig(
            $this->replacePlaceholders($config["dbFolderPath"])
        );
    }

    /**
     * @return GeneralConfigInterface
     */
    public function getGeneralConfig(): GeneralConfigInterface
    {
        return $this->generalConfig;
    }

    /**
     * Parse parser config block
     *
     * @param array $config
     * @throws GeneralException
     */
    public function parseParsersBlock(array $config)
    {
        foreach ($config as $name => $params) {
            // Check parser enabled status
            if (!array_key_exists("enabled", $params) || $params["enabled"] !== true) {
                continue;
            }

            // Check type
            if (!array_key_exists("type", $params)) {
                throw new GeneralException("Invalid type in parser - `{$name}`");
            }
            $type = new ParserType($params["type"]);

            // Check folder
            if (!array_key_exists("folderPath", $params)) {
                throw new GeneralException("Invalid folder path in parser - `{$name}`");
            }
            $folderPath = $this->replacePlaceholders($params["folderPath"]);
            if ($this->filesystem->exists(PROJECT_ROOT . $params["folderPath"]) === false) {
                throw new GeneralException("Folder is not exists in parser - `{$name}`");
            }

            // Parse files list
            $filesList = array_key_exists("files", $params)
                ? array_map(function ($elm) {
                    return $this->replacePlaceholders($elm);
                }, $params["files"])
                : [];

            // Parse sub variables list
            $subVariablesList = array_key_exists("subVariables", $params)
                ? array_map(function ($elm) {
                    return $this->replacePlaceholders($elm);
                }, $params["subVariables"])
                : [];


            $this->initParser(
                new ParserConfig(
                    $type,
                    $name,
                    $folderPath,
                    $filesList,
                    $subVariablesList
                )
            );
        }
    }

    /**
     * Init parser by requested configs
     *
     * @param ParserConfigInterface $parserConfig
     * @throws GeneralException
     */
    public function initParser(ParserConfigInterface $parserConfig)
    {
        switch (true) {
            case $parserConfig->getType()->isEquals(ParserType::YAML):
                $parserName = strtoupper($parserConfig->getName());

                if (array_key_exists($parserName, $this->parserList)) {
                    throw new GeneralException("Parser with the same name is already registered - `{$parserName}`");
                }

                $this->parserList[$parserName] = new YamlParser(
                    $this->yaml,
                    $this->filesystem,
                    $parserConfig
                );

                break;

            default:
                throw new GeneralException("Trying to parse with unsupported parser");
        }
    }

    /**
     * Get parameter from parser
     *
     * @param string $name
     * @param string $parserName
     * @return mixed
     * @throws GeneralException
     */
    public function getFromParser(string $name, string $parserName)
    {
        if (!array_key_exists($parserName, $this->parserList)) {
            throw new GeneralException("Trying to get unregistered parser - `{$parserName}`");
        }

        return $this->parserList[$parserName]->get($name);
    }

    /**
     * Parse dbd block and init PDO connections by configs
     *
     * @param array $config
     */
    public function parseDbBlock(array $config)
    {
        foreach ($config as $name => $params) {
            // Host
            if (!array_key_exists("host", $params)) {
                throw new \InvalidArgumentException("Mandatory field is missing - `host`");
            }
            $host = $this->replacePlaceholders($params["host"]);
            if (!is_string($host)) {
                throw new NotStringException("params->host");
            }
            if ($host === "") {
                throw new EmptyStringException("params->host");
            }

            // User
            if (!array_key_exists("user", $params)) {
                throw new \InvalidArgumentException("Mandatory field is missing - `user`");
            }
            $user = $this->replacePlaceholders($params["user"]);
            if (!is_string($user)) {
                throw new NotStringException("params->user");
            }
            if ($user === "") {
                throw new EmptyStringException("params->user");
            }

            // Pass
            if (!array_key_exists("pass", $params)) {
                throw new \InvalidArgumentException("Mandatory field is missing - `user`");
            }
            $pass = $this->replacePlaceholders($params["pass"]);
            if (!is_string($pass)) {
                throw new NotStringException("params->user");
            }

            // Port
            if (!array_key_exists("port", $params)) {
                throw new \InvalidArgumentException("Mandatory field is missing - `port`");
            }
            $port = intval($this->replacePlaceholders($params["port"]));
            if ($port <= 0) {
                throw new NotPositiveNumericException("params->charset");
            }

            // Charset
            if (!array_key_exists("charset", $params)) {
                throw new \InvalidArgumentException("Mandatory field is missing - `charset`");
            }
            $charset = $this->replacePlaceholders($params["charset"]);
            if (!is_string($params["charset"])) {
                throw new NotStringException("params->charset");
            }
            if ($charset === "") {
                throw new EmptyStringException("params->charset");
            }

            $pdo = new \PDO(
                "mysql:host={$host};port={$port};charset={$charset}",
                $user,
                $pass
            );
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(\PDO::ATTR_AUTOCOMMIT, 1);

            $this->dbConnection->setConnection($name, $pdo);
        }
    }

    /**
     * Replace ENV and PARSER variables
     *
     * @param string $string
     * @return mixed
     */
    public function replacePlaceholders(string $string)
    {
        return preg_replace_callback_array(
            [
                "/%ENV:([a-zA-Z0-9-_\.]+)%/" => function ($matches) {
                    return $this->getEnvironment(end($matches));
                },

                "/%PARSER:([a-zA-Z0-9-_\.]+):(.+)%/" => function ($matches) {
                    return $this->getFromParser($matches[2], strtoupper($matches[1]));
                },
            ],
            $string
        );
    }
}
