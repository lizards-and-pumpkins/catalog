<?php

namespace LizardsAndPumpkins\Product\ProductSearch;

use LizardsAndPumpkins\MasterFactory;
use LizardsAndPumpkins\Product\Composite\ConfigurableProduct;
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
        $realMethods = get_class_methods(MasterFactory::class);
        $testMethods = [
            'createDefaultSearchableAttributeValueCollector',
            'createConfigurableProductSearchableAttributeValueCollector'
        ];
        $stubFactory = $this->getMockBuilder(MasterFactory::class)
            ->setMethods(array_merge($realMethods, $testMethods))
            ->getMock();
        $stubFactory->method('createDefaultSearchableAttributeValueCollector')
            ->willReturn(new DefaultSearchableAttributeValueCollector());
        $stubFactory->method('createConfigurableProductSearchableAttributeValueCollector')
            ->willReturn(new ConfigurableProductSearchableAttributeValueCollector());
        $this->locator = new SearchableAttributeValueCollectorLocator($stubFactory);
    }

    public function testItReturnsADefaultCollector()
    {
        $product = $this->getMock(Product::class);
        $result = $this->locator->forProduct($product);
        $this->assertInstanceOf(DefaultSearchableAttributeValueCollector::class, $result);
    }

    public function testItReturnsAConfigurableProductAttributeValueCollectorForAConfigurableProduct()
    {
        $configurableProduct = $this->getMock(ConfigurableProduct::class, [], [], '', false);
        $result = $this->locator->forProduct($configurableProduct);
        $this->assertInstanceOf(ConfigurableProductSearchableAttributeValueCollector::class, $result);
    }
}
