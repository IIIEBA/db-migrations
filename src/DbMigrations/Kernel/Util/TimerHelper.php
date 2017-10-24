<?php

declare(strict_types=1);

namespace DbMigrations\Kernel\Util;

use BaseExceptions\Exception\InvalidArgument\EmptyStringException;
use DbMigrations\Kernel\Exception\GeneralException;

/**
 * Class TimerHelper
 * @package DbMigrations\Kernel\Util
 */
class TimerHelper
{
    /**
     * @var float[]
     */
    private $timersMap = [];

    /**
     * Start new timer
     *
     * @param string|null $name
     * @return string
     * @throws GeneralException
     */
    public function start(string $name = null): string
    {
        if ($name !== null) {
            if ($name === "") {
                throw new EmptyStringException("name");
            }
        } else {
            $name = md5(microtime(true));
        }

        if (array_key_exists($name, $this->timersMap)) {
            throw new GeneralException("Timer with the same name is already exists - [{$name}]");
        }

        $this->timersMap[$name] = microtime(true);

        return $name;
    }

    /**
     * Get timer value
     *
     * @param string $name
     * @return float
     * @throws GeneralException
     */
    public function end(string $name): float
    {
        if ($name === "") {
            throw new EmptyStringException("name");
        }

        if (array_key_exists($name, $this->timersMap) === false) {
            throw new GeneralException("Timer with requested name is not exists - [{$name}]");
        }

        $diff = microtime(true) - $this->timersMap[$name];
        unset($this->timersMap[$name]);

        return $diff;
    }
}
