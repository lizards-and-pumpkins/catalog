<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Product\Image;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Import\Product\ProductAttribute;
use LizardsAndPumpkins\Import\Product\ProductId;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Import\Product\Image\ProductImageListBuilder
 * @uses   \LizardsAndPumpkins\Import\Product\ProductAttributeListBuilder
 * @uses   \LizardsAndPumpkins\Import\Product\Image\ProductImageBuilder
 * @uses   \LizardsAndPumpkins\Import\Product\ProductId
 * @uses   \LizardsAndPumpkins\Import\Product\ProductAttribute
 * @uses   \LizardsAndPumpkins\Import\Product\ProductAttributeList
 * @uses   \LizardsAndPumpkins\Import\Product\AttributeCode
 * @uses   \LizardsAndPumpkins\Import\Product\Image\ProductImageList
 * @uses   \LizardsAndPumpkins\Import\Product\Image\ProductImage
 */
class ProductImageListBuilderTest extends TestCase
{
    /**
     * @var ProductId
     */
    private $testProductId;

    /**
     * @param ProductImageListBuilder $productImageListBuilder
     * @return ProductImageBuilder[]
     */
    private function getImageBuilderArrayFromInstance(ProductImageListBuilder $productImageListBuilder) : array
    {
        return $this->getPropertyFromInstance($productImageListBuilder, 'imageBuilders');
    }

    /**
     * @param ProductImageListBuilder $productImageListBuilder
     * @param string $attributeCode
     * @return mixed
     */
    private function getPropertyFromInstance(ProductImageListBuilder $productImageListBuilder, string $attributeCode)
    {
        $property = new \ReflectionProperty($productImageListBuilder, $attributeCode);
        $property->setAccessible(true);

        return $property->getValue($productImageListBuilder);
    }

    /**
     * @param string $fileName
     * @param string $label
     * @return array[]
     */
    private function getImageAttributeArray(string $fileName, string $label) : array
    {
        return [
            [
                ProductAttribute::CODE => ProductImage::FILE,
                ProductAttribute::VALUE => $fileName,
                ProductAttribute::CONTEXT => [],
            ],
            [
                ProductAttribute::CODE => ProductImage::LABEL,
                ProductAttribute::VALUE => $label,
                ProductAttribute::CONTEXT => [],
            ],
        ];
    }

    final protected function setUp(): void
    {
        $this->testProductId = new ProductId('test-sku');
    }

    public function testItReturnsAProductImageListBuilderInstance(): void
    {
        $productImageList = ProductImageListBuilder::fromImageArrays($this->testProductId);
        $this->assertInstanceOf(ProductImageListBuilder::class, $productImageList);
    }

    public function testItCreatesTheCorrectCountOfImages(): void
    {
        $productImageListArray = [
            $this->getImageAttributeArray('test1.jpg', 'The label A'),
            $this->getImageAttributeArray('test2.jpg', 'The label B')
        ];
        $productImageListBuilder = ProductImageListBuilder::fromImageArrays(
            $this->testProductId,
            ...$productImageListArray
        );
        $imageBuilders = $this->getImageBuilderArrayFromInstance($productImageListBuilder);

        $this->assertCount(2, $imageBuilders);
    }

    public function testItReturnsAProductImageListInstance(): void
    {
        $productImageListBuilder = ProductImageListBuilder::fromImageArrays($this->testProductId);
        $stubContext = $this->createMock(Context::class);
        $productImageList = $productImageListBuilder->getImageListForContext($stubContext);
        $this->assertInstanceOf(ProductImageList::class, $productImageList);
    }

    public function testItExtractsTheRightAmountOfImages(): void
    {
        $productImageListArray = [
            $this->getImageAttributeArray('test1.jpg', 'The label A'),
            $this->getImageAttributeArray('test2.jpg', 'The label B')
        ];
        $productImageListBuilder = ProductImageListBuilder::fromImageArrays(
            $this->testProductId,
            ...$productImageListArray
        );
        $imageList = $productImageListBuilder->getImageListForContext($this->createMock(Context::class));

        $this->assertCount(2, $imageList);
    }
}
