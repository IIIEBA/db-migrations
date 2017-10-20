<?php

declare(strict_types=1);

namespace DbMigrations\Kernel\Map;

/**
 * Class HashMapInterface
 * @package DbMigrations\Kernel\Map
 */
interface HashMapInterface
{
    /**
     * Converts object to hashmap
     *
     * @param object $object
     * @return array
     */
    public function convertToHashmap($object) : array;

    /**
     * Converts hashmap to object
     *
     * @param array $map
     * @return object
     */
    public function convertToObject(array $map);
}
