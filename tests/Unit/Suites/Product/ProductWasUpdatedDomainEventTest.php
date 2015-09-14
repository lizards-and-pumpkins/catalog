<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\DomainEvent;

/**
 * @covers \LizardsAndPumpkins\Product\ProductWasUpdatedDomainEvent
 */
class ProductWasUpdatedDomainEventTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductSource|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubProductSource;

    /**
     * @var ProductWasUpdatedDomainEvent
     */
    private $domainEvent;

    protected function setUp()
    {
        /** @var ProductId|\PHPUnit_Framework_MockObject_MockObject $stubProductId */
        $stubProductId = $this->getMock(ProductId::class, [], [], '', false);
        $this->stubProductSource = $this->getMock(ProductSource::class, [], [], '', false);
        $this->domainEvent = new ProductWasUpdatedDomainEvent($stubProductId, $this->stubProductSource);
    }

    public function testDomainEventInterfaceIsImplemented()
    {
        $this->assertInstanceOf(DomainEvent::class, $this->domainEvent);
    }

    public function testProductSourceIsReturned()
    {
        $result = $this->domainEvent->getProductSource();
        $this->assertSame($this->stubProductSource, $result);
    }
}
