<?php

namespace LizardsAndPumpkins\Http\ContentDelivery\ProductJsonService;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Util\Factory\MasterFactory;

/**
 * @covers \LizardsAndPumpkins\Http\ContentDelivery\ProductJsonService\EnrichProductJsonWithPricesBuilder
 */
class EnrichProductJsonWithPricesBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testRetrievalOfEnrichProductJsonWithPricesIsDelegatedToMasterFactory()
    {
        $stubEnrichProductJsonWithPrices = $this->createMock(EnrichProductJsonWithPrices::class);
        $stubContext = $this->createMock(Context::class);

        $stubMasterFactory = $this->getMockBuilder(MasterFactory::class)
            ->setMethods(['createEnrichProductJsonWithPrices', 'register'])->getMock();
        $stubMasterFactory->method('createEnrichProductJsonWithPrices')->with($stubContext)
            ->willReturn($stubEnrichProductJsonWithPrices);

        $builder = new EnrichProductJsonWithPricesBuilder($stubMasterFactory);

        $result = $builder->getForContext($stubContext);

        $this->assertSame($stubEnrichProductJsonWithPrices, $result);
    }
}
