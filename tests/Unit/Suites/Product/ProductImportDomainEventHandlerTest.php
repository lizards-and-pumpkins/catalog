<?php

namespace Brera\Product;

use Brera\VersionedEnvironmentBuilder;
use Brera\Environment;

/**
 * @covers \Brera\Product\ProductImportDomainEventHandler
 */
class ProductImportDomainEventHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function itShouldTriggerAProjection()
    {
        $stubProduct = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();

        $stubDomainEvent = $this->getMockBuilder(ProductImportDomainEvent::class)
            ->disableOriginalConstructor()
            ->getMock();
        $stubDomainEvent->expects($this->once())
            ->method('getXml');

        $stubProductBuilder = $this->getMock(ProductBuilder::class);
        $stubProductBuilder->expects($this->once())
            ->method('createProductFromXml')
            ->willReturn($stubProduct);

        $stubEnvironmentBuilder = $this->getMockBuilder(VersionedEnvironmentBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $stubEnvironmentBuilder->expects($this->any())->method('createEnvironmentFromXml')
            ->willReturn($this->getMock(Environment::class));

        $stubProjector = $this->getMockBuilder(ProductProjector::class)
            ->disableOriginalConstructor()
            ->getMock();
        $stubProjector->expects($this->once())
            ->method('project');

        (new ProductImportDomainEventHandler(
            $stubDomainEvent,
            $stubProductBuilder,
            $stubEnvironmentBuilder,
            $stubProjector)
        )->process();
    }
}
