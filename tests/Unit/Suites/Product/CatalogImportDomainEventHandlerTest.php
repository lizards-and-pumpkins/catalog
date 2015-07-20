<?php

namespace Brera\Product;

use Brera\Image\ImageImportDomainEvent;
use Brera\Queue\Queue;

/**
 * @covers \Brera\Product\CatalogImportDomainEventHandler
 * @uses   \Brera\Product\ProductListingWasUpdatedDomainEvent
 * @uses   \Brera\Product\ProductWasUpdatedDomainEvent
 * @uses   \Brera\Image\ImageImportDomainEvent
 * @uses   \Brera\Utils\XPathParser
 */
class CatalogImportDomainEventHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CatalogImportDomainEventHandler
     */
    private $catalogImportDomainEventHandler;

    /**
     * @var \PHPUnit_Framework_MockObject_Matcher_AnyInvokedCount
     */
    private $eventSpy;

    protected function setUp()
    {
        $xml = file_get_contents(__DIR__ . '/../../../shared-fixture/catalog.xml');

        $mockCatalogImportDomainEvent = $this->getMock(CatalogImportDomainEvent::class, [], [], '', false);
        $mockCatalogImportDomainEvent->method('getXml')
            ->willReturn($xml);

        $this->eventSpy = $this->any();

        $mockEventQueue = $this->getMock(Queue::class);
        $mockEventQueue->expects($this->eventSpy)
            ->method('add');

        $this->catalogImportDomainEventHandler = new CatalogImportDomainEventHandler(
            $mockCatalogImportDomainEvent,
            $mockEventQueue
        );
    }

    public function testProductWasUpdatedDomainEventsAreEmitted()
    {
        $this->catalogImportDomainEventHandler->process();

        $this->assertEventWasAddedToAQueue(ProductWasUpdatedDomainEvent::class);
    }

    public function testProductListingWasUpdatedDomainEventsAreEmitted()
    {
        $this->catalogImportDomainEventHandler->process();

        $this->assertEventWasAddedToAQueue(ProductListingWasUpdatedDomainEvent::class);
    }

    public function testImageImportDomainEventsAreEmitted()
    {
        $this->catalogImportDomainEventHandler->process();

        $this->assertEventWasAddedToAQueue(ImageImportDomainEvent::class);
    }

    /**
     * @param string $eventClass
     */
    private function assertEventWasAddedToAQueue($eventClass)
    {
        $numberOfRequiredInvocations = 0;

        /** @var \PHPUnit_Framework_MockObject_Invocation_Object $invocation */
        foreach ($this->eventSpy->getInvocations() as $invocation) {
            if ($eventClass === get_class($invocation->parameters[0])) {
                $numberOfRequiredInvocations++;
            }
        }

        $this->assertGreaterThan(
            0,
            $numberOfRequiredInvocations,
            sprintf('Failed to assert that %s was added to event queue.', $eventClass)
        );
    }
}
