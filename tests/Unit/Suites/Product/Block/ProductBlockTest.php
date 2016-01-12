<?php

namespace LizardsAndPumpkins\Product\Block;

use LizardsAndPumpkins\Http\HttpUrl;
use LizardsAndPumpkins\Product\Product;
use LizardsAndPumpkins\Product\ProductId;
use LizardsAndPumpkins\Projection\Catalog\ProductView;
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
     * @var ProductView|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubProductView;

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
        $this->stubProductView = $this->getMock(ProductView::class);

        $this->productBlock = new ProductBlock($this->stubBlockRenderer, 'foo.phtml', 'foo', $this->stubProductView);
    }

    public function testBlockClassIsExtended()
    {
        $this->assertInstanceOf(Block::class, $this->productBlock);
    }

    public function testFirstValueOfProductAttributeIsReturned()
    {
        $attributeCode = 'name';
        $attributeValue = 'foo';

        $this->stubProductView->method('getFirstValueOfAttribute')->with($attributeCode)->willReturn($attributeValue);
        $result = $this->productBlock->getFirstValueOfProductAttribute($attributeCode);

        $this->assertEquals($attributeValue, $result);
    }

    public function testImplodedValuesOfProductAttributeAreReturned()
    {
        $attributeCode = 'foo';
        $attributeValueA = 'bar';
        $attributeValueB = 'baz';
        $glue = ' in love with ';

        $this->stubProductView->method('getAllValuesOfAttribute')->willReturn([$attributeValueA, $attributeValueB]);

        $result = $this->productBlock->getImplodedValuesOfProductAttribute($attributeCode, $glue);
        $expected = $attributeValueA . $glue . $attributeValueB;

        $this->assertSame($expected, $result);
    }

    public function testProductIdIsReturned()
    {
        $stubProductId = $this->getMock(ProductId::class, [], [], '', false);

        $this->stubProductView->method('getId')->willReturn($stubProductId);
        $result = $this->productBlock->getProductId();

        $this->assertEquals($stubProductId, $result);
    }

    public function testProductUrlIsReturned()
    {
        $urlKey = 'foo';
        $testBaseUrl = '/lizards-and-pumpkins/';

        $this->stubBlockRenderer->method('getBaseUrl')->willReturn($testBaseUrl);
        $this->stubProductView->method('getFirstValueOfAttribute')->with(Product::URL_KEY)->willReturn($urlKey);
        $result = $this->productBlock->getProductUrl();

        $this->assertEquals($testBaseUrl . $urlKey, $result);
    }

    public function testGettingMainImageLabelIsDelegatedToProduct()
    {
        $testImageLabel = 'foo';
        $this->stubProductView->method('getMainImageLabel')->willReturn($testImageLabel);

        $this->assertSame($testImageLabel, $this->productBlock->getMainProductImageLabel());
    }

    public function testGettingMainImageFileNameIsDelegatedToProduct()
    {
        $testImageUrl = $this->getMock(HttpUrl::class, [], [], '', false);
        $this->stubProductView->method('getMainImageUrl')->willReturn($testImageUrl);

        $variantCode = 'small';
        $this->assertSame($testImageUrl, $this->productBlock->getMainProductImageUrl($variantCode));
    }

    public function testGettingProductImageCountIsDelegatedToProduct()
    {
        $testImagesCount = 3;
        $this->stubProductView->method('getImageCount')->willReturn($testImagesCount);

        $this->assertSame($testImagesCount, $this->productBlock->getProductImageCount());
    }

    public function testGettingProductImageFileNameIsDelegatedToProduct()
    {
        $testUrl = $this->getMock(HttpUrl::class, [], [], '', false);
        $variantCode = 'medium';
        $this->stubProductView->method('getImageUrlByNumber')->willReturn($testUrl);

        $this->assertSame($testUrl, $this->productBlock->getProductImageUrlByNumber(0, $variantCode));
    }

    public function testProductStockQuantityIsReturned()
    {
        $testStockQuantity = 3;
        $this->stubProductView->method('getFirstValueOfAttribute')->with('stock_qty')->willReturn($testStockQuantity);

        $this->assertSame($testStockQuantity, $this->productBlock->getProductStockQuantity());
    }
}
