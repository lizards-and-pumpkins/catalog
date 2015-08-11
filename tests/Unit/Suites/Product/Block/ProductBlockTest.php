<?php

namespace Brera\Product\Block;

use Brera\Image;
use Brera\Product\Product;
use Brera\Product\ProductAttribute;
use Brera\Product\ProductAttributeList;
use Brera\Product\ProductId;
use Brera\Renderer\Block;
use Brera\Renderer\BlockRenderer;
use Brera\TestFileFixtureTrait;

/**
 * @covers \Brera\Product\Block\ProductBlock
 * @uses   \Brera\Renderer\Block
 * @uses   \Brera\Image
 */
class ProductBlockTest extends \PHPUnit_Framework_TestCase
{
    use TestFileFixtureTrait;

    /**
     * @var Product|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubProduct;

    /**
     * @var ProductBlock
     */
    private $productBlock;

    protected function setUp()
    {
        /** @var  $stubBlockRenderer BlockRenderer|\PHPUnit_Framework_MockObject_MockObject */
        $stubBlockRenderer = $this->getMock(BlockRenderer::class, [], [], '', false);
        $this->stubProduct = $this->getMock(Product::class, [], [], '', false);

        $this->productBlock = new ProductBlock($stubBlockRenderer, 'foo.phtml', 'foo', $this->stubProduct);
    }

    public function testBlockClassIsExtended()
    {
        $this->assertInstanceOf(Block::class, $this->productBlock);
    }

    public function testFirstValueOfProductAttributeIsReturned()
    {
        $attributeCode = 'name';
        $attributeValue = 'foo';

        $this->stubProduct->method('getFirstValueOfAttribute')->with($attributeCode)->willReturn($attributeValue);
        $result = $this->productBlock->getFirstValueOfProductAttribute($attributeCode);

        $this->assertEquals($attributeValue, $result);
    }

    public function testAllValuesOfProductAttributeGluedAreReturned()
    {
        $attributeCode = 'foo';
        $attributeValueA = 'bar';
        $attributeValueB = 'baz';
        $glue = ' in love with ';

        $this->stubProduct->method('getAllValuesOfAttribute')->willReturn([$attributeValueA, $attributeValueB]);

        $result = $this->productBlock->getAllValuesOfProductAttributeGlued($attributeCode, $glue);
        $expected = $attributeValueA . $glue . $attributeValueB;

        $this->assertSame($expected, $result);
    }

    public function testProductIdIsReturned()
    {
        $stubProductId = $this->getMock(ProductId::class, [], [], '', false);

        $this->stubProduct->method('getId')->willReturn($stubProductId);
        $result = $this->productBlock->getProductId();

        $this->assertEquals($stubProductId, $result);
    }

    public function testProductUrlIsReturned()
    {
        $urlKey = 'foo';

        $this->stubProduct->method('getFirstValueOfAttribute')->with('url_key')->willReturn($urlKey);
        $result = $this->productBlock->getProductUrl();

        $this->assertEquals('/brera/' . $urlKey, $result);
    }

    public function testEmptyStringIsReturnedIfProductBrandLogoImageFileDoesNotExist()
    {
        $testProductBrandName = 'foo';
        $this->stubProduct->method('getFirstValueOfAttribute')->with('brand')->willReturn($testProductBrandName);

        $result = $this->productBlock->getBrandLogoSrc();

        $this->assertEquals('', $result);
    }

    public function testProductBrandLogoSrcIsReturned()
    {
        $testProductBrandName = 'foo';
        $this->stubProduct->method('getFirstValueOfAttribute')->with('brand')->willReturn($testProductBrandName);

        $expectedProductBrandLogoSrc = 'images/brands/brands-slider/' . $testProductBrandName . '.png';
        $this->createFixtureFile('pub/' . $expectedProductBrandLogoSrc, '');

        $result = $this->productBlock->getBrandLogoSrc();

        $this->assertEquals($expectedProductBrandLogoSrc, $result);
    }

    public function testInstanceOfImageIsReturned()
    {
        $stubAttribute = $this->getMock(ProductAttribute::class, [], [], '', false);

        $mockProductAttributeList = $this->getMock(ProductAttributeList::class);
        $mockProductAttributeList->expects($this->exactly(2))
            ->method('getAttributesWithCode')
            ->willReturn([$stubAttribute]);

        $this->stubProduct->expects($this->once())
            ->method('getFirstValueOfAttribute')
            ->with('image')
            ->willReturn($mockProductAttributeList);

        $result = $this->productBlock->getMainProductImage();

        $this->assertInstanceOf(Image::class, $result);
    }
}
