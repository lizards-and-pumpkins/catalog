<?php

namespace LizardsAndPumpkins\Import\Product;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Import\Product\Composite\AssociatedProductList;
use LizardsAndPumpkins\Import\Product\Composite\AssociatedProductListBuilder;
use LizardsAndPumpkins\Import\Product\Composite\ConfigurableProduct;
use LizardsAndPumpkins\Import\Product\Composite\ProductVariationAttributeList;

/**
 * @covers \LizardsAndPumpkins\Import\Product\ConfigurableProductBuilder
 * @uses   \LizardsAndPumpkins\Import\Product\Composite\AssociatedProductListBuilder
 * @uses   \LizardsAndPumpkins\Import\Product\Composite\AssociatedProductList
 * @uses   \LizardsAndPumpkins\Import\Product\Composite\ConfigurableProduct
 */
class ConfigurableProductBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SimpleProductBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubSimpleProductBuilder;

    /**
     * @var ProductVariationAttributeList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubVariationAttributeList;

    /**
     * @var AssociatedProductListBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubAssociatedProductListBuilder;

    /**
     * @var ProductAvailability|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubProductAvailability;

    /**
     * @var ConfigurableProductBuilder
     */
    private $configurableProductBuilder;

    protected function setUp()
    {
        $this->stubSimpleProductBuilder = $this->createMock(SimpleProductBuilder::class);
        $this->stubSimpleProductBuilder->method('getProductForContext')->willReturn(
            $this->createMock(SimpleProduct::class)
        );

        $this->stubVariationAttributeList = $this->createMock(ProductVariationAttributeList::class);
        $this->stubVariationAttributeList->method('getAttributes')->willReturn(['test']);

        $this->stubAssociatedProductListBuilder = $this->createMock(AssociatedProductListBuilder::class);
        $this->stubAssociatedProductListBuilder->method('getAssociatedProductListForContext')->willReturn(
            $this->createMock(AssociatedProductList::class)
        );
        
        $this->stubProductAvailability = $this->createMock(ProductAvailability::class);
        
        $this->configurableProductBuilder = new ConfigurableProductBuilder(
            $this->stubSimpleProductBuilder,
            $this->stubVariationAttributeList,
            $this->stubAssociatedProductListBuilder,
            $this->stubProductAvailability
        );
    }

    public function testItImplementsTheProductBuilderInterface()
    {
        $this->assertInstanceOf(ProductBuilder::class, $this->configurableProductBuilder);
    }

    public function testItReturnsAConfigurableProductInstanceForTheGivenContext()
    {
        /** @var Context|\PHPUnit_Framework_MockObject_MockObject $stubContext */
        $stubContext = $this->createMock(Context::class);
        
        $result = $this->configurableProductBuilder->getProductForContext($stubContext);
        
        $this->assertInstanceOf(ConfigurableProduct::class, $result);
    }

    public function testProductIsNotAvailableIfTheSimpleProductBuilderReturnsFalse()
    {
        /** @var Context|\PHPUnit_Framework_MockObject_MockObject $stubContext */
        $stubContext = $this->createMock(Context::class);
        
        $this->stubSimpleProductBuilder->method('isAvailableForContext')->willReturn(false);

        $this->assertFalse($this->configurableProductBuilder->isAvailableForContext($stubContext));
    }

    public function testProductIsNotAvailableIfAssociatedProductsMissVariationAttributes()
    {
        /** @var Context|\PHPUnit_Framework_MockObject_MockObject $stubContext */
        $stubContext = $this->createMock(Context::class);
        
        $this->stubSimpleProductBuilder->method('isAvailableForContext')->willReturn(true);

        /** @var SimpleProduct|\PHPUnit_Framework_MockObject_MockObject $mockProduct */
        $mockProduct = $this->stubSimpleProductBuilder->getProductForContext($stubContext);
        $mockProduct->method('hasAttribute')->willReturn(false);

        /** @var AssociatedProductList|\PHPUnit_Framework_MockObject_MockObject $mockProductList */
        $mockProductList = $this->stubAssociatedProductListBuilder->getAssociatedProductListForContext($stubContext);
        $mockProductList->method('getProducts')->willReturn([$mockProduct]);

        $this->assertFalse($this->configurableProductBuilder->isAvailableForContext($stubContext));
    }

    public function testProductIsAvailableIfAssociatedProductsHaveAllVariationAttributes()
    {
        /** @var Context|\PHPUnit_Framework_MockObject_MockObject $stubContext */
        $stubContext = $this->createMock(Context::class);
        
        $this->stubSimpleProductBuilder->method('isAvailableForContext')->willReturn(true);

        /** @var SimpleProduct|\PHPUnit_Framework_MockObject_MockObject $mockProduct */
        $mockProduct = $this->stubSimpleProductBuilder->getProductForContext($stubContext);
        $mockProduct->method('hasAttribute')->willReturn(true);

        /** @var AssociatedProductList|\PHPUnit_Framework_MockObject_MockObject $mockProductList */
        $mockProductList = $this->stubAssociatedProductListBuilder->getAssociatedProductListForContext($stubContext);
        $mockProductList->method('getProducts')->willReturn([$mockProduct]);

        $this->assertTrue($this->configurableProductBuilder->isAvailableForContext($stubContext));
    }
}
