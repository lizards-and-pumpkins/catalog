<?php

namespace LizardsAndPumpkins\Product\Block;

use LizardsAndPumpkins\Product\Product;
use LizardsAndPumpkins\Product\ProductId;
use LizardsAndPumpkins\Renderer\Block;
use LizardsAndPumpkins\Renderer\BlockRenderer;
use LizardsAndPumpkins\TestFileFixtureTrait;

/**
 * @covers \LizardsAndPumpkins\Product\Block\ProductBlock
 * @uses   \LizardsAndPumpkins\Product\ProductAttributeList
 * @uses   \LizardsAndPumpkins\Renderer\Block
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

    /**
     * @var BlockRenderer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubBlockRenderer;

    protected function setUp()
    {
        $this->stubBlockRenderer = $this->getMock(BlockRenderer::class, [], [], '', false);
        $this->stubProduct = $this->getMock(Product::class);

        $this->productBlock = new ProductBlock(
            $this->stubBlockRenderer,
            'foo.phtml',
            'foo',
            $this->stubProduct
        );
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

    public function testImplodedValuesOfProductAttributeAreReturned()
    {
        $attributeCode = 'foo';
        $attributeValueA = 'bar';
        $attributeValueB = 'baz';
        $glue = ' in love with ';

        $this->stubProduct->method('getAllValuesOfAttribute')->willReturn([$attributeValueA, $attributeValueB]);

        $result = $this->productBlock->getImplodedValuesOfProductAttribute($attributeCode, $glue);
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
        $testBaseUrl = '/lizards-and-pumpkins/';
        
        $this->stubBlockRenderer->method('getBaseUrl')->willReturn($testBaseUrl);
        $this->stubProduct->method('getFirstValueOfAttribute')->with(Product::URL_KEY)->willReturn($urlKey);
        $result = $this->productBlock->getProductUrl();

        $this->assertEquals($testBaseUrl . $urlKey, $result);
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
        $testBaseUrl = '/lizards-and-pumpkins/';
        
        $this->stubBlockRenderer->method('getBaseUrl')->willReturn($testBaseUrl);
        $this->stubProduct->method('getFirstValueOfAttribute')->with('brand')->willReturn($testProductBrandName);

        $brandLogoSrc = 'images/brands/brands-slider/' . $testProductBrandName . '.png';
        $this->createFixtureFile('pub/' . $brandLogoSrc, '');

        $expectedProductBrandLogoSrc = $testBaseUrl . $brandLogoSrc;
        $result = $this->productBlock->getBrandLogoSrc();

        $this->assertEquals($expectedProductBrandLogoSrc, $result);
    }

    public function testGettingMainImageLabelIsDelegatedToProduct()
    {
        $testImageLabel = 'foo';
        $this->stubProduct->method('getMainImageLabel')->willReturn($testImageLabel);

        $this->assertSame($testImageLabel, $this->productBlock->getMainProductImageLabel());
    }

    public function testGettingMainImageFileNameIsDelegatedToProduct()
    {
        $testImageFileName = 'foo.png';
        $this->stubProduct->method('getMainImageFileName')->willReturn($testImageFileName);

        $this->assertSame($testImageFileName, $this->productBlock->getMainProductFileName());
    }

    public function testGettingProductImageCountIsDelegatedToProduct()
    {
        $testImagesCount = 3;
        $this->stubProduct->method('getImageCount')->willReturn($testImagesCount);

        $this->assertSame($testImagesCount, $this->productBlock->getProductImageCount());
    }

    public function testGettingProductImageFileNameIsDelegatedToProduct()
    {
        $testFileName = 'foo.png';
        $this->stubProduct->method('getImageFileNameByNumber')->willReturn($testFileName);

        $this->assertSame($testFileName, $this->productBlock->getProductImageFileNameByNumber(0));
    }
}
