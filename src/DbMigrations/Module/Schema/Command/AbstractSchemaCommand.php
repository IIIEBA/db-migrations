<?php

declare(strict_types=1);

namespace DbMigrations\Module\Schema\Command;

use DbMigrations\Kernel\Command\AbstractCommand;
use DbMigrations\Kernel\Util\StdInHelper;
use DbMigrations\Module\Schema\Component\SchemaComponentInterface;
use Psr\Log\LoggerInterface;

/**
 * Class AbstractSchemaCommand
 * @package Module\Schema\Command
 */
class AbstractSchemaCommand extends AbstractCommand
{
    /**
     * @var SchemaComponentInterface
     */
    private $schemaComponent;

    /**
     * AbstractSchemaCommand constructor.
     *
     * @param SchemaComponentInterface $schemaComponent
     * @param StdInHelper $stdInHelper
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        SchemaComponentInterface $schemaComponent,
        StdInHelper $stdInHelper,
        LoggerInterface $logger = null
    ) {
        $this->schemaComponent = $schemaComponent;

        parent::__construct($stdInHelper, $logger);
    }

    /**
     * @return SchemaComponentInterface
     */
    public function getSchemaComponent(): SchemaComponentInterface
    {
        return $this->schemaComponent;
    }
}
