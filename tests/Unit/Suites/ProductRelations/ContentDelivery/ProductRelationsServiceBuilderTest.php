<?php

namespace LizardsAndPumpkins\ProductRelations\ContentDelivery;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Util\Factory\SampleMasterFactory;

/**
 * @covers \LizardsAndPumpkins\ProductRelations\ContentDelivery\ProductRelationsServiceBuilder
 */
class ProductRelationsServiceBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testRetrievalOfProductRelationsServiceIsDelegatedToMasterFactory()
    {
        $stubProductRelationsService = $this->createMock(ProductRelationsService::class);
        $stubContext = $this->createMock(Context::class);

        $stubMasterFactory = $this->getMockBuilder(SampleMasterFactory::class)
            ->setMethods(['createProductRelationsService'])->getMock();
        $stubMasterFactory->method('createProductRelationsService')->with($stubContext)
            ->willReturn($stubProductRelationsService);

        $builder = new ProductRelationsServiceBuilder($stubMasterFactory);

        $result = $builder->getForContext($stubContext);

        $this->assertSame($stubProductRelationsService, $result);
    }
}
