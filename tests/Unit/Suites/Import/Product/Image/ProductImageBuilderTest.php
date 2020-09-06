<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Product\Image;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Import\Product\Image\Exception\InvalidProductImageAttributeListException;
use LizardsAndPumpkins\Import\Product\ProductAttribute;
use LizardsAndPumpkins\Import\Product\ProductId;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Import\Product\Image\ProductImageBuilder
 * @uses   \LizardsAndPumpkins\Import\Product\ProductAttributeListBuilder
 * @uses   \LizardsAndPumpkins\Import\Product\ProductId
 * @uses   \LizardsAndPumpkins\Import\Product\Image\ProductImage
 * @uses   \LizardsAndPumpkins\Import\Product\ProductAttributeList
 * @uses   \LizardsAndPumpkins\Import\Product\ProductAttribute
 * @uses   \LizardsAndPumpkins\Import\Product\AttributeCode
 */
class ProductImageBuilderTest extends TestCase
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

    final protected function setUp(): void
    {
        $this->testProductId = new ProductId('test-sku');
    }
    
    public function testItReturnsAProductImageBuilderInstanceFromNamedConstructor(): void
    {
        $productImageBuilder = ProductImageBuilder::fromArray($this->testProductId, $this->testAttributeArray);
        $this->assertInstanceOf(ProductImageBuilder::class, $productImageBuilder);
    }

    public function testItThrowsAnExceptionIfThereIsNoFileAttribute(): void
    {
        $this->expectException(InvalidProductImageAttributeListException::class);
        $this->expectExceptionMessage('The image attribute "file" is missing for product "test-sku"');
        ProductImageBuilder::fromArray($this->testProductId, []);
    }

    public function testItReturnsAProductImageForAGivenContext(): void
    {
        /** @var Context|MockObject $stubContext */
        $stubContext = $this->createMock(Context::class);
        $productImageBuilder = ProductImageBuilder::fromArray($this->testProductId, $this->testAttributeArray);
        $this->assertInstanceOf(ProductImage::class, $productImageBuilder->getImageForContext($stubContext));
    }
}
