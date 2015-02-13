<?php

namespace Brera\Product;

use Brera\Environment\EnvironmentSource;
use Brera\Environment\EnvironmentSourceBuilder;

/**
 * @covers \Brera\Product\ProductImportDomainEventHandler
 */
class ProductImportDomainEventHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function itShouldTriggerProjectionAndSearchIndexing()
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

        $stubEnvironmentSource = $this->getMockBuilder(EnvironmentSource::class)
            ->disableOriginalConstructor()
            ->getMock();

        $stubEnvironmentSourceBuilder = $this->getMockBuilder(EnvironmentSourceBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $stubEnvironmentSourceBuilder->expects($this->any())->method('createFromXml')
            ->willReturn($stubEnvironmentSource);

        $stubProjector = $this->getMockBuilder(ProductProjector::class)
            ->disableOriginalConstructor()
            ->getMock();
        $stubProjector->expects($this->once())
            ->method('project')
            ->with($stubProductSource, $stubEnvironmentSource);

        $stubSearchIndexer = $this->getMockBuilder(ProductSearchDocumentBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $stubSearchIndexer->expects($this->once())
            ->method('aggregate')
            ->with($stubProductSource, $stubEnvironmentSource);

        (new ProductImportDomainEventHandler(
            $stubDomainEvent,
            $stubProductBuilder,
            $stubEnvironmentSourceBuilder,
            $stubProjector,
            $stubSearchIndexer
        )
        )->process();
    }
}
