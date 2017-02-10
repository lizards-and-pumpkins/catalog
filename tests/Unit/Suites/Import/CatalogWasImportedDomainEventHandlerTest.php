<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import;

use LizardsAndPumpkins\Messaging\Event\DomainEventHandler;

use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Import\CatalogWasImportedDomainEventHandler
 * @uses   \LizardsAndPumpkins\Import\ContentBlock\UpdateContentBlockCommand
 * @uses   \LizardsAndPumpkins\Messaging\Queue\Message
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageMetadata
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageName
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessagePayload
 * @uses   \LizardsAndPumpkins\Context\DataVersion\DataVersion
 * @uses   \LizardsAndPumpkins\Import\CatalogWasImportedDomainEvent
 */
class CatalogWasImportedDomainEventHandlerTest extends TestCase
{
    /**
     * @var CatalogWasImportedDomainEventHandler
     */
    private $eventHandler;
    
    protected function setUp()
    {
        $this->eventHandler = new CatalogWasImportedDomainEventHandler();
    }

    public function testItIsAnDomainEventHandler()
    {
        $this->assertInstanceOf(DomainEventHandler::class, $this->eventHandler);
    }
}
