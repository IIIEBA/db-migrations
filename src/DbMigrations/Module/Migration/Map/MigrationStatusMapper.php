<?php

declare(strict_types=1);

namespace DbMigrations\Module\Migration\Map;

use BaseExceptions\Exception\InvalidArgument\NotObjectException;
use DbMigrations\Kernel\Map\HashMapInterface;
use DbMigrations\Module\Migration\Enum\MigrationStatusType;
use DbMigrations\Module\Migration\Model\MigrationStatus;
use DbMigrations\Module\Migration\Model\MigrationStatusInterface;

/**
 * Class MigrationStatusMapper
 * @package DbMigrations\Module\Migration\Map
 */
class MigrationStatusMapper implements HashMapInterface
{
    /**
     * Converts object to hashmap
     *
     * @param MigrationStatusInterface $object
     * @return array
     */
    public function convertToHashmap($object): array
    {
        if ($object instanceof MigrationStatusInterface === false) {
            throw new NotObjectException("object", $object);
        }

        return [
            "migrationId" => $object->getMigrationId(),
            "name" => $object->getName(),
            "filename" => $object->getFilename(),
            "type" => $object->getType()->getValue(),
            "startedAt" => $object->getStartedAt(),
            "appliedAt" => $object->getAppliedAt(),
            "id" => $object->getId(),
        ];
    }

    /**
     * Converts hashmap to object
     *
     * @param array $map
     * @return object
     */
    public function convertToObject(array $map)
    {
        return new MigrationStatus(
            $map["migrationId"],
            $map["name"],
            array_key_exists("filename", $map) ? $map["filename"] : null,
            array_key_exists("type", $map) ? new MigrationStatusType($map["type"]) : null,
            $map["startedAt"] !== null ? floatval($map["startedAt"]) : null,
            $map["appliedAt"] !== null ? floatval($map["appliedAt"]) : null,
            $map["id"] !== null ? intval($map["id"]) : null
        );
    }
}
