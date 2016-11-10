<?php

namespace LizardsAndPumpkins\Http\ContentDelivery\ProductJsonService;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Util\Factory\SampleMasterFactory;

/**
 * @covers \LizardsAndPumpkins\Http\ContentDelivery\ProductJsonService\ProductJsonServiceBuilder
 */
class ProductJsonServiceBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testRetrievalOfProductJsonServiceIsDelegatedToMasterFactory()
    {
        $stubProductJsonService = $this->createMock(ProductJsonService::class);
        $stubContext = $this->createMock(Context::class);

        $stubMasterFactory = $this->getMockBuilder(SampleMasterFactory::class)
            ->setMethods(['createProductJsonService'])->getMock();
        $stubMasterFactory->method('createProductJsonService')->with($stubContext)
            ->willReturn($stubProductJsonService);

        $builder = new ProductJsonServiceBuilder($stubMasterFactory);

        $result = $builder->getForContext($stubContext);

        $this->assertSame($stubProductJsonService, $result);
    }
}
