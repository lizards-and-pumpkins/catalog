<?php

namespace Brera\Product;

use Brera\Image\ImageImportDomainEvent;
use Brera\Queue\Queue;

/**
 * @covers \Brera\Product\CatalogImportDomainEventHandler
 * @uses   \Brera\Product\ProductImportDomainEvent
 * @uses   \Brera\Image\ImageImportDomainEvent
 * @uses   \Brera\XPathParser
 */
class CatalogImportDomainEventHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CatalogImportDomainEventHandler
     */
    private $catalogImportDomainEventHandler;

    /**
     * @var Queue|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockEventQueue;

    /**
     * @var \PHPUnit_Framework_MockObject_Matcher_AnyInvokedCount
     */
    private $eventSpy;

    protected function setUp()
    {
        $xml = file_get_contents(__DIR__ . '/../../../shared-fixture/product.xml');

        $mockCatalogImportDomainEvent = $this->getMock(CatalogImportDomainEvent::class, [], [], '', false);
        $mockCatalogImportDomainEvent->expects($this->any())
            ->method('getXml')
            ->willReturn($xml);

        $this->eventSpy = $this->any();

        $this->mockEventQueue = $this->getMock(Queue::class);
        $this->mockEventQueue->expects($this->eventSpy)
            ->method('add');

        $this->catalogImportDomainEventHandler = new CatalogImportDomainEventHandler(
            $mockCatalogImportDomainEvent,
            $this->mockEventQueue
        );
    }

    /**
     * @test
     */
    public function itShouldEmitProductImportDomainEvents()
    {
        $this->catalogImportDomainEventHandler->process();

        $this->assertEventWasAddedToAQueue(ProductImportDomainEvent::class);
    }

    /**
     * @test
     */
    public function itShouldEmitImageImportDomainEvents()
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
