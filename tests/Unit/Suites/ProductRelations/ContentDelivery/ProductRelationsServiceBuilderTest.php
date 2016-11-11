<?php

namespace LizardsAndPumpkins\ProductRelations\ContentDelivery;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Util\Factory\MasterFactory;

/**
 * @covers \LizardsAndPumpkins\ProductRelations\ContentDelivery\ProductRelationsServiceBuilder
 */
class ProductRelationsServiceBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testRetrievalOfProductRelationsServiceIsDelegatedToMasterFactory()
    {
        $stubProductRelationsService = $this->createMock(ProductRelationsService::class);
        $stubContext = $this->createMock(Context::class);

        $stubMasterFactory = $this->getMockBuilder(MasterFactory::class)
            ->setMethods(['createProductRelationsService', 'register'])->getMock();
        $stubMasterFactory->method('createProductRelationsService')->with($stubContext)
            ->willReturn($stubProductRelationsService);

        $builder = new ProductRelationsServiceBuilder($stubMasterFactory);

        $result = $builder->getForContext($stubContext);

        $this->assertSame($stubProductRelationsService, $result);
    }
}
