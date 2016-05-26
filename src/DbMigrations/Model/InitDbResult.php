<?php

namespace DbMigrations\Model;

use BaseExceptions\Exception\InvalidArgument\EmptyStringException;
use BaseExceptions\Exception\InvalidArgument\NotStringException;

/**
 * Class InitDbResult
 * @package DbMigrations\Model
 */
class InitDbResult implements InitDbResultInterface
{
    /**
     * @var InitTableResultInterface[]
     */
    private $list;

    /**
     * InitDbResult constructor.
     *
     * @param InitTableResultInterface[] $tableResultList
     */
    public function __construct(array $tableResultList = [])
    {
        $list = [];
        foreach ($tableResultList as $elm) {
            if ($elm instanceof InitTableResultInterface === false) {
                throw new \InvalidArgumentException("Elm must be instance of InitTableResultInterface");
            }

            $list[$elm->getTableName()] = $elm;
        }

        $this->list = $list;
    }

    /**
     * Add table result
     *
     * @param InitTableResultInterface $result
     */
    public function addTableResult(InitTableResultInterface $result)
    {
        $this->list[$result->getTableName()] = $result;
    }

    /**
     * Get table result by name
     *
     * @param string $name
     * @return InitTableResultInterface|null
     */
    public function getTableResult($name) {
        if (!is_string($name)) {
            throw new NotStringException("name");
        }
        if ($name === "") {
            throw new EmptyStringException("name");
        }

        return array_key_exists($name, $this->list) ? $this->list[$name] : null;
    }

    /**
     * Get result for all tables
     *
     * @return InitTableResultInterface[]
     */
    public function getResult()
    {
        asort($this->list);

        return $this->list;
    }
}
