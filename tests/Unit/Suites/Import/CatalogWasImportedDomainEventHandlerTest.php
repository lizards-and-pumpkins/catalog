<?php

namespace LizardsAndPumpkins\Import;

use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Messaging\Event\DomainEventHandler;
use LizardsAndPumpkins\Messaging\Queue\Message;

/**
 * @covers \LizardsAndPumpkins\Import\CatalogWasImportedDomainEventHandler
 * @uses   \LizardsAndPumpkins\Import\ContentBlock\UpdateContentBlockCommand
 * @uses   \LizardsAndPumpkins\Messaging\Queue\Message
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageMetadata
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageName
 * @uses   \LizardsAndPumpkins\Context\DataVersion\DataVersion
 * @uses   \LizardsAndPumpkins\Import\CatalogWasImportedDomainEvent
 */
class CatalogWasImportedDomainEventHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CatalogWasImportedDomainEventHandler
     */
    private $eventHandler;

    /**
     * @var Message|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubEvent;

    /**
     * @var DataVersion|\PHPUnit_Framework_MockObject_MockObject
     */
    private $testVersion;

    protected function setUp()
    {
        $this->testVersion = DataVersion::fromVersionString('foo');
        $this->stubEvent = $this->getMock(Message::class, [], [], '', false);
        $this->stubEvent->method('getName')->willReturn('catalog_was_imported');
        $this->stubEvent->method('getMetadata')->willReturn(['data_version' => (string)$this->testVersion]);

        $this->eventHandler = new CatalogWasImportedDomainEventHandler($this->stubEvent);
    }

    public function testItIsAnDomainEventHandler()
    {
        $this->assertInstanceOf(DomainEventHandler::class, $this->eventHandler);
    }

    public function testItTriggersTheProductListingProjection()
    {
        $this->markTestIncomplete('This event handler is currently not doing anything.');
        $this->eventHandler->process();
    }
}
