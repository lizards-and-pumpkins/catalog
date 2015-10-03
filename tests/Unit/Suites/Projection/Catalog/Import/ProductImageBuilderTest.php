<?php


namespace LizardsAndPumpkins\Projection\Catalog\Import;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Product\ProductAttribute;
use LizardsAndPumpkins\Product\ProductId;
use LizardsAndPumpkins\Product\ProductImage;
use LizardsAndPumpkins\Projection\Catalog\Import\Exception\InvalidProductImageAttributeListException;

/**
 * @covers \LizardsAndPumpkins\Projection\Catalog\Import\ProductImageBuilder
 * @uses   \LizardsAndPumpkins\Projection\Catalog\Import\ProductAttributeListBuilder
 * @uses   \LizardsAndPumpkins\Product\ProductId
 * @uses   \LizardsAndPumpkins\Product\ProductImage
 * @uses   \LizardsAndPumpkins\Product\ProductAttributeList
 * @uses   \LizardsAndPumpkins\Product\ProductAttribute
 * @uses   \LizardsAndPumpkins\Product\AttributeCode
 */
class ProductImageBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductId
     */
    private $testProductId;
    
    private $testAttributeArray = [
        [
            ProductAttribute::CODE => ProductImage::FILE,
            ProductAttribute::CONTEXT_DATA => [],
            ProductAttribute::VALUE => 'test.jpg'
        ]
    ];

    protected function setUp()
    {
        $this->testProductId = ProductId::fromString('test-sku');
    }
    
    public function testItReturnsAProductImageBuilderInstanceFromNamedConstructor()
    {
        $productImageBuilder = ProductImageBuilder::fromArray($this->testProductId, $this->testAttributeArray);
        $this->assertInstanceOf(ProductImageBuilder::class, $productImageBuilder);
    }

    public function testItThrowsAnExceptionIfThereIsNoFileAttribute()
    {
        $this->setExpectedException(
            InvalidProductImageAttributeListException::class,
            'The image attribute "file" is missing for product "test-sku"'
        );
        ProductImageBuilder::fromArray($this->testProductId, []);
    }

    public function testItReturnsAProductImageForAGivenContext()
    {
        /** @var Context|\PHPUnit_Framework_MockObject_MockObject $stubContext */
        $stubContext = $this->getMock(Context::class);
        $productImageBuilder = ProductImageBuilder::fromArray($this->testProductId, $this->testAttributeArray);
        $this->assertInstanceOf(ProductImage::class, $productImageBuilder->getImageForContext($stubContext));
    }
}
