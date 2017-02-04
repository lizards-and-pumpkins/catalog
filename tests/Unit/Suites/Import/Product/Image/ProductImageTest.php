<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Product\Image;

use LizardsAndPumpkins\Import\Product\ProductAttribute;
use LizardsAndPumpkins\Import\Product\ProductAttributeList;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Import\Product\Image\ProductImage
 * @uses   \LizardsAndPumpkins\Import\Product\ProductAttributeList
 * @uses   \LizardsAndPumpkins\Import\Product\ProductAttribute
 * @uses   \LizardsAndPumpkins\Import\Product\AttributeCode
 */
class ProductImageTest extends TestCase
{
    /**
     * @var ProductAttributeList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubAttributeList;

    /**
     * @var ProductImage
     */
    private $productImage;

    private function addStubAttributeWithCodeAndValue(string $attributeCode, string $attributeValue)
    {
        $stubAttribute = $this->createMock(ProductAttribute::class);
        $stubAttribute->method('getValue')->willReturn($attributeValue);
        $this->stubAttributeList->method('hasAttribute')->with($attributeCode)->willReturn(true);
        $this->stubAttributeList->method('getAttributesWithCode')->with($attributeCode)
            ->willReturn([$stubAttribute]);
    }

    protected function setUp()
    {
        $this->stubAttributeList = $this->createMock(ProductAttributeList::class);
        $this->productImage = new ProductImage($this->stubAttributeList);
    }

    public function testItReturnsTheFileName()
    {
        $testFileName = 'test.jpg';
        $this->addStubAttributeWithCodeAndValue(ProductImage::FILE, $testFileName);
        $this->assertSame($testFileName, $this->productImage->getFileName());
    }

    public function testItReturnsAnEmptyStringIfThereIsNoLabel()
    {
        $this->assertSame('', $this->productImage->getLabel());
    }

    public function testItReturnsTheAttributeLabel()
    {
        $testLabel = 'Image Label';
        $this->addStubAttributeWithCodeAndValue(ProductImage::LABEL, $testLabel);
        $this->assertSame($testLabel, $this->productImage->getLabel());
    }

    public function testItIsJsonSerializable()
    {
        $this->assertInstanceOf(\JsonSerializable::class, $this->productImage);
    }

    public function testItCanBeJsonSerializedAndRehydrated()
    {
        $imageFileAttribute = ProductAttribute::fromArray([
            ProductAttribute::CODE => ProductImage::FILE,
            ProductAttribute::CONTEXT => [],
            ProductAttribute::VALUE => 'test.png'
        ]);
        $imageLabelAttribute = ProductAttribute::fromArray([
            ProductAttribute::CODE => ProductImage::LABEL,
            ProductAttribute::CONTEXT => [],
            ProductAttribute::VALUE => 'Foo bar buz'
        ]);
        $sourceProductImage = new ProductImage(new ProductAttributeList($imageFileAttribute, $imageLabelAttribute));
        
        $json = json_encode($sourceProductImage);
        $rehydratedProductImage = ProductImage::fromArray(json_decode($json, true));
        
        $this->assertSame($sourceProductImage->getFileName(), $rehydratedProductImage->getFileName());
        $this->assertSame($sourceProductImage->getLabel(), $rehydratedProductImage->getLabel());
    }
}
