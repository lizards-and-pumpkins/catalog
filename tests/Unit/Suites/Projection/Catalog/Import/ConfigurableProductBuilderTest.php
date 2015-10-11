<?php


namespace LizardsAndPumpkins\Projection\Catalog\Import;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Product\Composite\AssociatedProductList;
use LizardsAndPumpkins\Product\Composite\ConfigurableProduct;
use LizardsAndPumpkins\Product\Composite\ProductVariationAttributeList;
use LizardsAndPumpkins\Product\SimpleProduct;

/**
 * @covers \LizardsAndPumpkins\Projection\Catalog\Import\ConfigurableProductBuilder
 * @uses   \LizardsAndPumpkins\Projection\Catalog\Import\AssociatedProductListBuilder
 * @uses   \LizardsAndPumpkins\Product\Composite\AssociatedProductList
 * @uses   \LizardsAndPumpkins\Product\Composite\ConfigurableProduct
 */
class ConfigurableProductBuilderTest extends \PHPUnit_Framework_TestCase
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
        $this->mockSimpleProductBuilder = $this->getMock(SimpleProductBuilder::class, [], [], '', false);
        $this->mockSimpleProductBuilder->method('getProductForContext')->willReturn(
            $this->getMock(SimpleProduct::class, [], [], '', false)
        );

        $this->mockVariationAttributeList = $this->getMock(ProductVariationAttributeList::class, [], [], '', false);
        $this->mockVariationAttributeList->method('getAttributes')->willReturn([]);

        $this->mockAssociatedProductListBuilder = $this->getMock(AssociatedProductListBuilder::class);
        $this->mockAssociatedProductListBuilder->method('getAssociatedProductListForContext')->willReturn(
            $this->getMock(AssociatedProductList::class)
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
        $stubContext = $this->getMock(Context::class);
        
        $result = $this->configurableProductBuilder->getProductForContext($stubContext);
        
        $this->assertInstanceOf(ConfigurableProduct::class, $result);
    }
}
