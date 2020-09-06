<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductDetail;

use LizardsAndPumpkins\Import\Price\PriceSnippetRenderer;
use LizardsAndPumpkins\Import\Product\ProductJsonSnippetRenderer;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\ProductDetail\ProductDetailPageMetaInfoSnippetContent
 * @uses   \LizardsAndPumpkins\Import\SnippetContainer
 * @uses   \LizardsAndPumpkins\Util\SnippetCodeValidator
 */
class ProductDetailPageMetaInfoSnippetContentTest extends TestCase
{
    /**
     * @var ProductDetailPageMetaInfoSnippetContent
     */
    private $pageMetaInfo;

    /**
     * @var string
     */
    private $rootSnippetCode = 'root-snippet-code';

    /**
     * @var string
     */
    private $sourceId = '123';

    private $containers = ['additional_info' => []];

    private $pageSpecificData = [['foo' => 'bar'], ['baz' => 'qux']];

    final protected function setUp(): void
    {
        $this->pageMetaInfo = ProductDetailPageMetaInfoSnippetContent::create(
            $this->sourceId,
            $this->rootSnippetCode,
            [$this->rootSnippetCode],
            $this->containers,
            $this->pageSpecificData
        );
    }

    public function testReturnsPageMetaSnippetAsArray(): void
    {
        $this->assertIsArray($this->pageMetaInfo->toArray());
    }

    public function testExpectedArrayKeysArePresentInJsonContent(): void
    {
        $keys = [
            ProductDetailPageMetaInfoSnippetContent::KEY_PRODUCT_ID,
            ProductDetailPageMetaInfoSnippetContent::KEY_ROOT_SNIPPET_CODE,
            ProductDetailPageMetaInfoSnippetContent::KEY_PAGE_SNIPPET_CODES,
            ProductDetailPageMetaInfoSnippetContent::KEY_CONTAINER_SNIPPETS,
        ];

        foreach ($keys as $key) {
            $this->assertArrayHasKey(
                $key,
                $this->pageMetaInfo->toArray(),
                sprintf('The expected key "%s" is not set on the page meta info array', $key)
            );
        }
    }

    public function testExceptionIsThrownIfTheSourceIdIsNotScalar(): void
    {
        $this->expectException(\TypeError::class);
        ProductDetailPageMetaInfoSnippetContent::create([], 'test', [], [], []);
    }

    public function testExceptionIsThrownIfRootSnippetCodeIsNoString(): void
    {
        $this->expectException(\TypeError::class);
        ProductDetailPageMetaInfoSnippetContent::create('foo', 1.0, [], [], []);
    }

    public function testRootSnippetCodeIsAddedToSnippetCodeListIfNotPresent(): void
    {
        $rootSnippetCode = 'root-snippet-code';
        $pageMetaInfo = ProductDetailPageMetaInfoSnippetContent::create('123', $rootSnippetCode, [], [], []);

        $this->assertTrue(in_array($rootSnippetCode, $pageMetaInfo->getPageSnippetCodes()));
    }

    public function testFromJsonConstructorIsPresent(): void
    {
        $pageMeta = json_decode(json_encode($this->pageMetaInfo->toArray()), true);
        $pageMetaInfo = ProductDetailPageMetaInfoSnippetContent::fromArray($pageMeta);
        $this->assertInstanceOf(ProductDetailPageMetaInfoSnippetContent::class, $pageMetaInfo);
    }

    /**
     * @dataProvider pageInfoArrayKeyProvider
     * @param string $key
     */
    public function testExceptionIsThrownIfRequiredKeyIsMissing(string $key): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Missing key in input array');

        $pageMeta = $this->pageMetaInfo->toArray();
        unset($pageMeta[$key]);

        ProductDetailPageMetaInfoSnippetContent::fromArray($pageMeta);
    }

    /**
     * @return array[]
     */
    public function pageInfoArrayKeyProvider() : array
    {
        return [
            [ProductDetailPageMetaInfoSnippetContent::KEY_PRODUCT_ID],
            [ProductDetailPageMetaInfoSnippetContent::KEY_ROOT_SNIPPET_CODE],
            [ProductDetailPageMetaInfoSnippetContent::KEY_PAGE_SNIPPET_CODES],
            [ProductDetailPageMetaInfoSnippetContent::KEY_CONTAINER_SNIPPETS],
            [ProductDetailPageMetaInfoSnippetContent::KEY_PAGE_SPECIFIC_DATA],
        ];
    }

    public function testSourceIdIsReturned(): void
    {
        $this->assertEquals($this->sourceId, $this->pageMetaInfo->getProductId());
    }

    public function testRootSnippetCodeIsReturned(): void
    {
        $this->assertEquals($this->rootSnippetCode, $this->pageMetaInfo->getRootSnippetCode());
    }

    public function testPageSnippetCodeListIsReturned(): void
    {
        $this->assertIsArray($this->pageMetaInfo->getPageSnippetCodes());
    }

    public function testThePageSnippetListIncludesTheProductJsonAndPriceSnippetCodes(): void
    {
        $pageSnippetCodes = $this->pageMetaInfo->getPageSnippetCodes();

        $this->assertTrue(in_array(ProductJsonSnippetRenderer::CODE, $pageSnippetCodes));
        $this->assertTrue(in_array(PriceSnippetRenderer::PRICE, $pageSnippetCodes));
        $this->assertTrue(in_array(PriceSnippetRenderer::SPECIAL_PRICE, $pageSnippetCodes));
    }

    public function testItReturnsThePageSnippetContainers(): void
    {
        $this->assertSame($this->containers, $this->pageMetaInfo->getContainerSnippets());
    }

    public function testReturnsPageSpecificData(): void
    {
        $this->assertSame($this->pageSpecificData, $this->pageMetaInfo->getPageSpecificData());
    }
}
