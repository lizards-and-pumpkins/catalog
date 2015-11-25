<?php


namespace LizardsAndPumpkins\Projection\Catalog\Import;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Product\Composite\AssociatedProductList;
use LizardsAndPumpkins\Product\Product;

/**
 * @covers \LizardsAndPumpkins\Projection\Catalog\Import\AssociatedProductListBuilder
 * @uses   \LizardsAndPumpkins\Product\Composite\AssociatedProductList
 */
class AssociatedProductListBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AssociatedProductListBuilder
     */
    private $builder;

    /**
     * @var ProductBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubProductBuilder;

    protected function setUp()
    {
        $stubProduct = $this->getMock(Product::class);
        $stubProduct->method('getId')->willReturnCallback(function () {
            return uniqid();
        });
        $this->stubProductBuilder = $this->getMock(ProductBuilder::class);
        $this->stubProductBuilder->method('getProductForContext')->willReturn($stubProduct);
        $this->stubProductBuilder->method('isAvailableForContext')->willReturn(true);
        
        $this->builder = new AssociatedProductListBuilder(
            $this->stubProductBuilder,
            $this->stubProductBuilder
        );
    }
    
    public function testItReturnsAnAssociatedProductList()
    {
        $stubContext = $this->getMock(Context::class);
        $stubContext->method('__toString')->willReturn('test');
        
        $associatedProductList = $this->builder->getAssociatedProductListForContext($stubContext);
        
        $this->assertInstanceOf(AssociatedProductList::class, $associatedProductList);
        $this->assertCount(2, $associatedProductList);
    }
}
