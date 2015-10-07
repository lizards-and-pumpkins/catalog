<?php


namespace LizardsAndPumpkins\Projection\Catalog\Import;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Product\ProductAttribute;
use LizardsAndPumpkins\Product\ProductId;
use LizardsAndPumpkins\Product\ProductImage;
use LizardsAndPumpkins\Product\ProductImageList;

/**
 * @covers \LizardsAndPumpkins\Projection\Catalog\Import\ProductImageListBuilder
 * @uses   \LizardsAndPumpkins\Projection\Catalog\Import\ProductAttributeListBuilder
 * @uses   \LizardsAndPumpkins\Projection\Catalog\Import\ProductImageBuilder
 * @uses   \LizardsAndPumpkins\Product\ProductId
 * @uses   \LizardsAndPumpkins\Product\ProductAttribute
 * @uses   \LizardsAndPumpkins\Product\ProductAttributeList
 * @uses   \LizardsAndPumpkins\Product\AttributeCode
 * @uses   \LizardsAndPumpkins\Product\ProductImageList
 * @uses   \LizardsAndPumpkins\Product\ProductImage
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
    private function getImageBuilderArrayFromInstance(ProductImageListBuilder $productImageListBuilder)
    {
        return $this->getPropertyFromInstance($productImageListBuilder, 'imageBuilders');
    }

    /**
     * @param ProductImageListBuilder $productImageListBuilder
     * @param string $attributeCode
     * @return mixed
     */
    private function getPropertyFromInstance(ProductImageListBuilder $productImageListBuilder, $attributeCode)
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
    private function getImageAttributeArray($fileName, $label)
    {
        return [
            [
                ProductAttribute::CODE => ProductImage::FILE,
                ProductAttribute::VALUE => $fileName,
                ProductAttribute::CONTEXT_DATA => [],
            ],
            [
                ProductAttribute::CODE => ProductImage::LABEL,
                ProductAttribute::VALUE => $label,
                ProductAttribute::CONTEXT_DATA => [],
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
        $stubContext = $this->getMock(Context::class);
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
        $imageList = $productImageListBuilder->getImageListForContext($this->getMock(Context::class));
        $this->assertCount(2, $imageList);
    }
}
