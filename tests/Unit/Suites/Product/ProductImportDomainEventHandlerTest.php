<?php

namespace Brera\Product;

use Brera\Context\ContextSource;
use Brera\Context\ContextSourceBuilder;

/**
 * @covers \Brera\Product\ProductImportDomainEventHandler
 */
class ProductImportDomainEventHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function itShouldTriggerProjection()
    {
        $stubProductSource = $this->getMockBuilder(ProductSource::class)
            ->disableOriginalConstructor()
            ->getMock();

        $stubDomainEvent = $this->getMockBuilder(ProductImportDomainEvent::class)
            ->disableOriginalConstructor()
            ->getMock();
        $stubDomainEvent->expects($this->once())
            ->method('getXml');

        $stubProductBuilder = $this->getMock(ProductSourceBuilder::class);
        $stubProductBuilder->expects($this->once())
            ->method('createProductSourceFromXml')
            ->willReturn($stubProductSource);

        $stubContextSource = $this->getMockBuilder(ContextSource::class)
            ->disableOriginalConstructor()
            ->getMock();

        $stubContextSourceBuilder = $this->getMockBuilder(ContextSourceBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $stubContextSourceBuilder->expects($this->any())->method('createFromXml')
            ->willReturn($stubContextSource);

        $stubProjector = $this->getMockBuilder(ProductProjector::class)
            ->disableOriginalConstructor()
            ->getMock();
        $stubProjector->expects($this->once())
            ->method('project')
            ->with($stubProductSource, $stubContextSource);

        (new ProductImportDomainEventHandler(
            $stubDomainEvent,
            $stubProductBuilder,
            $stubContextSourceBuilder,
            $stubProjector
        )
        )->process();
    }
}
