<?php


namespace LizardsAndPumpkins\Import\Product\Image;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Import\Product\Image\ProductImageBuilder;
use LizardsAndPumpkins\Import\Product\ProductAttribute;
use LizardsAndPumpkins\Import\Product\ProductId;
use LizardsAndPumpkins\Import\Product\Image\ProductImage;
use LizardsAndPumpkins\Import\Product\Image\InvalidProductImageAttributeListException;

/**
 * @covers \LizardsAndPumpkins\Import\Product\Image\ProductImageBuilder
 * @uses   \LizardsAndPumpkins\Import\Product\ProductAttributeListBuilder
 * @uses   \LizardsAndPumpkins\Import\Product\ProductId
 * @uses   \LizardsAndPumpkins\Import\Product\Image\ProductImage
 * @uses   \LizardsAndPumpkins\Import\Product\ProductAttributeList
 * @uses   \LizardsAndPumpkins\Import\Product\ProductAttribute
 * @uses   \LizardsAndPumpkins\Import\Product\AttributeCode
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
            ProductAttribute::CONTEXT => [],
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
        $this->expectException(InvalidProductImageAttributeListException::class);
        $this->expectExceptionMessage('The image attribute "file" is missing for product "test-sku"');
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
