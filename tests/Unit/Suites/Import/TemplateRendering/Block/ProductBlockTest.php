<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\TemplateRendering\Block;

use LizardsAndPumpkins\Context\BaseUrl\HttpBaseUrl;
use LizardsAndPumpkins\Http\HttpUrl;
use LizardsAndPumpkins\Import\Product\Product;
use LizardsAndPumpkins\Import\Product\ProductId;
use LizardsAndPumpkins\Import\Product\View\ProductView;
use LizardsAndPumpkins\Import\TemplateRendering\Block;
use LizardsAndPumpkins\Import\TemplateRendering\BlockRenderer;
use LizardsAndPumpkins\TestFileFixtureTrait;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Import\TemplateRendering\Block\ProductBlock
 * @uses   \LizardsAndPumpkins\Context\BaseUrl\HttpBaseUrl
 * @uses   \LizardsAndPumpkins\Import\Product\ProductAttributeList
 * @uses   \LizardsAndPumpkins\Import\TemplateRendering\Block
 */
class ProductBlockTest extends TestCase
{
    use TestFileFixtureTrait;

    /**
     * @var ProductView|MockObject
     */
    private $stubProductView;

    /**
     * @var ProductBlock
     */
    private $productBlock;

    /**
     * @var BlockRenderer|MockObject
     */
    private $stubBlockRenderer;

    final protected function setUp(): void
    {
        $this->stubBlockRenderer = $this->createMock(BlockRenderer::class);
        $this->stubProductView = $this->createMock(ProductView::class);

        $this->productBlock = new ProductBlock($this->stubBlockRenderer, 'foo.phtml', 'foo', $this->stubProductView);
    }

    public function testBlockClassIsExtended(): void
    {
        $this->assertInstanceOf(Block::class, $this->productBlock);
    }

    public function testFirstValueOfProductAttributeIsReturned(): void
    {
        $attributeCode = 'name';
        $attributeValue = 'foo';

        $this->stubProductView->method('getFirstValueOfAttribute')->with($attributeCode)->willReturn($attributeValue);
        $result = $this->productBlock->getFirstValueOfProductAttribute($attributeCode);

        $this->assertEquals($attributeValue, $result);
    }

    public function testImplodedValuesOfProductAttributeAreReturned(): void
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

    public function testProductIdIsReturned(): void
    {
        $stubProductId = $this->createMock(ProductId::class);

        $this->stubProductView->method('getId')->willReturn($stubProductId);
        $result = $this->productBlock->getProductId();

        $this->assertEquals($stubProductId, $result);
    }

    public function testProductUrlIsReturned(): void
    {
        $urlKey = 'foo';
        $testBaseUrl = new HttpBaseUrl('http://example.com/');

        $this->stubBlockRenderer->method('getBaseUrl')->willReturn($testBaseUrl);
        $this->stubProductView->method('getFirstValueOfAttribute')->with(Product::URL_KEY)->willReturn($urlKey);
        $result = $this->productBlock->getProductUrl();

        $this->assertEquals($testBaseUrl . $urlKey, $result);
    }

    public function testGettingMainImageLabelIsDelegatedToProduct(): void
    {
        $testImageLabel = 'foo';
        $this->stubProductView->method('getMainImageLabel')->willReturn($testImageLabel);

        $this->assertSame($testImageLabel, $this->productBlock->getMainProductImageLabel());
    }

    public function testGettingMainImageFileNameIsDelegatedToProduct(): void
    {
        $testImageUrl = $this->createMock(HttpUrl::class);
        $this->stubProductView->method('getMainImageUrl')->willReturn($testImageUrl);

        $variantCode = 'small';
        $this->assertSame($testImageUrl, $this->productBlock->getMainProductImageUrl($variantCode));
    }

    public function testGettingProductImageCountIsDelegatedToProduct(): void
    {
        $testImagesCount = 3;
        $this->stubProductView->method('getImageCount')->willReturn($testImagesCount);

        $this->assertSame($testImagesCount, $this->productBlock->getProductImageCount());
    }

    public function testGettingProductImageFileNameIsDelegatedToProduct(): void
    {
        $testUrl = $this->createMock(HttpUrl::class);
        $variantCode = 'medium';
        $this->stubProductView->method('getImageUrlByNumber')->willReturn($testUrl);

        $this->assertSame($testUrl, $this->productBlock->getProductImageUrlByNumber(0, $variantCode));
    }

    public function testProductStockQuantityIsReturned(): void
    {
        $testStockQuantity = '3';
        $this->stubProductView->method('getFirstValueOfAttribute')->with('stock_qty')->willReturn($testStockQuantity);

        $this->assertSame($testStockQuantity, $this->productBlock->getProductStockQuantity());
    }
}
