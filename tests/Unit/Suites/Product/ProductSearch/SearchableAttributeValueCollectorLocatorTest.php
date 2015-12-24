<?php

namespace LizardsAndPumpkins\Product\ProductSearch;

use LizardsAndPumpkins\MasterFactory;
use LizardsAndPumpkins\Product\Product;

/**
 * @covers \LizardsAndPumpkins\Product\ProductSearch\SearchableAttributeValueCollectorLocator
 * @uses   \LizardsAndPumpkins\Product\ProductSearch\DefaultSearchableAttributeValueCollector
 * @uses   \LizardsAndPumpkins\Product\AttributeCode
 */
class SearchableAttributeValueCollectorLocatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SearchableAttributeValueCollectorLocator
     */
    private $locator;

    protected function setUp()
    {
        /** @var MasterFactory|\PHPUnit_Framework_MockObject_MockObject $stubFactory */
        $methods = get_class_methods(MasterFactory::class);
        $stubFactory = $this->getMockBuilder(MasterFactory::class)
            ->setMethods(array_merge($methods, ['createDefaultSearchableAttributeValueCollector']))
            ->getMock();
        $stubFactory->method('createDefaultSearchableAttributeValueCollector')
            ->willReturn(new DefaultSearchableAttributeValueCollector());
        $this->locator = new SearchableAttributeValueCollectorLocator($stubFactory);
    }

    public function testItReturnsADefault()
    {
        $product = $this->getMock(Product::class);
        $result = $this->locator->forProduct($product);
        $this->assertInstanceOf(DefaultSearchableAttributeValueCollector::class, $result);
    }
}
