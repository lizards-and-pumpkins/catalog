<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Product;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Import\Product\Composite\AssociatedProductList;
use LizardsAndPumpkins\Import\Product\Composite\AssociatedProductListBuilder;
use LizardsAndPumpkins\Import\Product\Composite\ConfigurableProduct;
use LizardsAndPumpkins\Import\Product\Composite\ProductVariationAttributeList;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Import\Product\ConfigurableProductBuilder
 * @uses   \LizardsAndPumpkins\Import\Product\AttributeCode
 * @uses   \LizardsAndPumpkins\Import\Product\Composite\AssociatedProductListBuilder
 * @uses   \LizardsAndPumpkins\Import\Product\Composite\AssociatedProductList
 * @uses   \LizardsAndPumpkins\Import\Product\Composite\ConfigurableProduct
 */
class ConfigurableProductBuilderTest extends TestCase
{
    /**
     * @var SimpleProductBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockSimpleProductBuilder;

    /**
     * @var ProductVariationAttributeList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockVariationAttributeList;

    /**
     * @var AssociatedProductListBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockAssociatedProductListBuilder;

    /**
     * @var ConfigurableProductBuilder
     */
    private $configurableProductBuilder;

    protected function setUp()
    {
        $this->mockSimpleProductBuilder = $this->createMock(SimpleProductBuilder::class);
        $this->mockSimpleProductBuilder->method('getProductForContext')->willReturn(
            $this->createMock(SimpleProduct::class)
        );

        $this->mockVariationAttributeList = $this->createMock(ProductVariationAttributeList::class);
        $this->mockVariationAttributeList->method('getAttributes')->willReturn([AttributeCode::fromString('test')]);

        $this->mockAssociatedProductListBuilder = $this->createMock(AssociatedProductListBuilder::class);
        $this->mockAssociatedProductListBuilder->method('getAssociatedProductListForContext')->willReturn(
            $this->createMock(AssociatedProductList::class)
        );


        $this->configurableProductBuilder = new ConfigurableProductBuilder(
            $this->mockSimpleProductBuilder,
            $this->mockVariationAttributeList,
            $this->mockAssociatedProductListBuilder
        );
    }

    public function testItImplementsTheProductBuilderInterface()
    {
        $this->assertInstanceOf(ProductBuilder::class, $this->configurableProductBuilder);
    }

    public function testItReturnsAConfigurableProductInstanceForTheGivenContext()
    {
        $stubContext = $this->createMock(Context::class);
        $result = $this->configurableProductBuilder->getProductForContext($stubContext);
        $this->assertInstanceOf(ConfigurableProduct::class, $result);
    }

    public function testProductIsNotAvailableIfTheSimpleProductBuilderReturnsFalse()
    {
        $this->mockSimpleProductBuilder->method('isAvailableForContext')->willReturn(false);
        $stubContext = $this->createMock(Context::class);

        $this->assertFalse($this->configurableProductBuilder->isAvailableForContext($stubContext));
    }

    public function testProductIsNotAvailableIfAssociatedProductsMissVariationAttributes()
    {
        $stubContext = $this->createMock(Context::class);
        
        $this->mockSimpleProductBuilder->method('isAvailableForContext')->willReturn(true);

        /** @var SimpleProduct|\PHPUnit_Framework_MockObject_MockObject $mockProduct */
        $mockProduct = $this->mockSimpleProductBuilder->getProductForContext($stubContext);
        $mockProduct->method('hasAttribute')->willReturn(false);

        /** @var AssociatedProductList|\PHPUnit_Framework_MockObject_MockObject $mockProductList */
        $mockProductList = $this->mockAssociatedProductListBuilder->getAssociatedProductListForContext($stubContext);
        $mockProductList->method('getProducts')->willReturn([$mockProduct]);

        $this->assertFalse($this->configurableProductBuilder->isAvailableForContext($stubContext));
    }

    public function testProductIsAvailableIfAssociatedProductsHaveAllVariationAttributes()
    {
        $stubContext = $this->createMock(Context::class);
        
        $this->mockSimpleProductBuilder->method('isAvailableForContext')->willReturn(true);

        /** @var SimpleProduct|\PHPUnit_Framework_MockObject_MockObject $mockProduct */
        $mockProduct = $this->mockSimpleProductBuilder->getProductForContext($stubContext);
        $mockProduct->method('hasAttribute')->willReturn(true);

        /** @var AssociatedProductList|\PHPUnit_Framework_MockObject_MockObject $mockProductList */
        $mockProductList = $this->mockAssociatedProductListBuilder->getAssociatedProductListForContext($stubContext);
        $mockProductList->method('getProducts')->willReturn([$mockProduct]);

        $this->assertTrue($this->configurableProductBuilder->isAvailableForContext($stubContext));
    }
}
