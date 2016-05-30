<?php

namespace LizardsAndPumpkins\Import;

use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Import\Exception\NoCatalogWasImportedDomainEventMessageException;
use LizardsAndPumpkins\Messaging\Event\DomainEventHandler;
use LizardsAndPumpkins\Messaging\Queue\Message;

/**
 * @covers \LizardsAndPumpkins\Import\CatalogWasImportedDomainEventHandler
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
    private $stubVersion;

    protected function setUp()
    {
        $this->stubVersion = $this->getMock(DataVersion::class, [], [], '', false);
        $this->stubEvent = $this->getMock(Message::class, [], [], '', false);
        $this->stubEvent->method('getName')->willReturn('catalog_was_imported_domain_event');
        $this->stubEvent->method('getPayload')->willReturn(json_encode(['data_version' => $this->stubVersion]));
        
        $this->eventHandler = new CatalogWasImportedDomainEventHandler($this->stubEvent);
    }
    
    public function testItIsAnDomainEventHandler()
    {
        $this->assertInstanceOf(DomainEventHandler::class, $this->eventHandler);
    }

    public function testThrowsExceptionIfDomainEventNameDoesNotMatch()
    {
        $this->expectException(NoCatalogWasImportedDomainEventMessageException::class);
        $this->expectExceptionMessage('Expected "catalog_was_imported" domain event, got "foo_domain_event"');

        /** @var Message|\PHPUnit_Framework_MockObject_MockObject $stubEvent */
        $stubEvent = $this->getMock(Message::class, [], [], '', false);
        $stubEvent->method('getName')->willReturn('foo_domain_event');
        
        new CatalogWasImportedDomainEventHandler($stubEvent);
    }

    public function testItTriggersTheProductListingProjection()
    {
        $this->markTestIncomplete('This event handler is currently not doing anything.');
        $this->eventHandler->process();
    }
}
