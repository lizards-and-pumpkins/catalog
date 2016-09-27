<?php

namespace LizardsAndPumpkins\Import\Product\Image;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Import\Product\ProductAttribute;
use LizardsAndPumpkins\Import\Product\ProductId;

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
class ProductImageListBuilderTest extends \PHPUnit_Framework_TestCase
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

    protected function setUp()
    {
        $this->testProductId = ProductId::fromString('test-sku');
    }

    public function testItReturnsAProductImageListBuilderInstance()
    {
        $productImageList = ProductImageListBuilder::fromArray($this->testProductId, []);
        $this->assertInstanceOf(ProductImageListBuilder::class, $productImageList);
    }

    public function testItCreatesTheCorrectCountOfImages()
    {
        $productImageListArray = [
            $this->getImageAttributeArray('test1.jpg', 'The label A'),
            $this->getImageAttributeArray('test2.jpg', 'The label B')
        ];
        $productImageListBuilder = ProductImageListBuilder::fromArray($this->testProductId, $productImageListArray);
        $imageBuilders = $this->getImageBuilderArrayFromInstance($productImageListBuilder);
        $this->assertCount(2, $imageBuilders);
    }

    public function testItReturnsAProductImageListInstance()
    {
        $productImageListBuilder = ProductImageListBuilder::fromArray($this->testProductId, []);
        $stubContext = $this->createMock(Context::class);
        $productImageList = $productImageListBuilder->getImageListForContext($stubContext);
        $this->assertInstanceOf(ProductImageList::class, $productImageList);
    }

    public function testItExtractsTheRightAmountOfImages()
    {
        $productImageListArray = [
            $this->getImageAttributeArray('test1.jpg', 'The label A'),
            $this->getImageAttributeArray('test2.jpg', 'The label B')
        ];
        $productImageListBuilder = ProductImageListBuilder::fromArray($this->testProductId, $productImageListArray);
        $imageList = $productImageListBuilder->getImageListForContext($this->createMock(Context::class));
        $this->assertCount(2, $imageList);
    }
}
