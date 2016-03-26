<?php

namespace LizardsAndPumpkins\Product\ProductSearch;

use LizardsAndPumpkins\ProductSearch\Import\AttributeValueCollectorLocator;
use LizardsAndPumpkins\ProductSearch\Import\ConfigurableProductAttributeValueCollector;
use LizardsAndPumpkins\ProductSearch\Import\DefaultAttributeValueCollector;
use LizardsAndPumpkins\Util\Factory\MasterFactory;
use LizardsAndPumpkins\Import\Product\Composite\ConfigurableProduct;
use LizardsAndPumpkins\Import\Product\Product;

/**
 * @covers \LizardsAndPumpkins\ProductSearch\Import\AttributeValueCollectorLocator
 * @uses   \LizardsAndPumpkins\ProductSearch\Import\DefaultAttributeValueCollector
 * @uses   \LizardsAndPumpkins\Import\Product\AttributeCode
 */
class AttributeValueCollectorLocatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AttributeValueCollectorLocator
     */
    private $locator;

    protected function setUp()
    {
        /** @var MasterFactory|\PHPUnit_Framework_MockObject_MockObject $stubFactory */
        $realMethods = get_class_methods(MasterFactory::class);
        $testMethods = [
            'createDefaultAttributeValueCollector',
            'createConfigurableProductAttributeValueCollector'
        ];
        $stubFactory = $this->getMockBuilder(MasterFactory::class)
            ->setMethods(array_merge($realMethods, $testMethods))
            ->getMock();
        $stubFactory->method('createDefaultAttributeValueCollector')
            ->willReturn(new DefaultAttributeValueCollector());
        $stubFactory->method('createConfigurableProductAttributeValueCollector')
            ->willReturn(new ConfigurableProductAttributeValueCollector());
        $this->locator = new AttributeValueCollectorLocator($stubFactory);
    }

    public function testItReturnsADefaultCollector()
    {
        $product = $this->getMock(Product::class);
        $result = $this->locator->forProduct($product);
        $this->assertInstanceOf(DefaultAttributeValueCollector::class, $result);
    }

    public function testItReturnsAConfigurableProductAttributeValueCollectorForAConfigurableProduct()
    {
        $configurableProduct = $this->getMock(ConfigurableProduct::class, [], [], '', false);
        $result = $this->locator->forProduct($configurableProduct);
        $this->assertInstanceOf(ConfigurableProductAttributeValueCollector::class, $result);
    }
}
