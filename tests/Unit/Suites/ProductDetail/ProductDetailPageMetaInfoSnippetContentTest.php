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

    protected function setUp()
    {
        $this->pageMetaInfo = ProductDetailPageMetaInfoSnippetContent::create(
            $this->sourceId,
            $this->rootSnippetCode,
            [$this->rootSnippetCode],
            $this->containers,
            $this->pageSpecificData
        );
    }

    public function testReturnsPageMetaSnippetAsArray()
    {
        $this->assertInternalType('array', $this->pageMetaInfo->toArray());
    }

    public function testExpectedArrayKeysArePresentInJsonContent()
    {
        $keys = [
            ProductDetailPageMetaInfoSnippetContent::KEY_PRODUCT_ID,
            ProductDetailPageMetaInfoSnippetContent::KEY_ROOT_SNIPPET_CODE,
            ProductDetailPageMetaInfoSnippetContent::KEY_PAGE_SNIPPET_CODES,
            ProductDetailPageMetaInfoSnippetContent::KEY_CONTAINER_SNIPPETS,
        ];
        foreach ($keys as $key) {
            $this->assertTrue(
                array_key_exists($key, $this->pageMetaInfo->toArray()),
                sprintf('The expected key "%s" is not set on the page meta info array', $key)
            );
        }
    }

    public function testExceptionIsThrownIfTheSourceIdIsNotScalar()
    {
        $this->expectException(\TypeError::class);
        ProductDetailPageMetaInfoSnippetContent::create([], 'test', [], [], []);
    }

    public function testExceptionIsThrownIfRootSnippetCodeIsNoString()
    {
        $this->expectException(\TypeError::class);
        ProductDetailPageMetaInfoSnippetContent::create('foo', 1.0, [], [], []);
    }

    public function testRootSnippetCodeIsAddedToSnippetCodeListIfNotPresent()
    {
        $rootSnippetCode = 'root-snippet-code';
        $pageMetaInfo = ProductDetailPageMetaInfoSnippetContent::create('123', $rootSnippetCode, [], [], []);
        $this->assertContains(
            $rootSnippetCode,
            $pageMetaInfo->getPageSnippetCodes()
        );
    }

    public function testFromJsonConstructorIsPresent()
    {
        $pageMetaInfo = ProductDetailPageMetaInfoSnippetContent::fromJson(json_encode($this->pageMetaInfo->toArray()));
        $this->assertInstanceOf(ProductDetailPageMetaInfoSnippetContent::class, $pageMetaInfo);
    }

    public function testExceptionIsThrownInCaseOfJsonErrors()
    {
        $this->expectException(\OutOfBoundsException::class);
        ProductDetailPageMetaInfoSnippetContent::fromJson('malformed-json');
    }

    /**
     * @dataProvider pageInfoArrayKeyProvider
     */
    public function testExceptionIsThrownIfRequiredKeyIsMissing(string $key)
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Missing key in input JSON');
        $pageInfo = $this->pageMetaInfo->toArray();
        unset($pageInfo[$key]);
        ProductDetailPageMetaInfoSnippetContent::fromJson(json_encode($pageInfo));
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

    public function testSourceIdIsReturned()
    {
        $this->assertEquals($this->sourceId, $this->pageMetaInfo->getProductId());
    }

    public function testRootSnippetCodeIsReturned()
    {
        $this->assertEquals($this->rootSnippetCode, $this->pageMetaInfo->getRootSnippetCode());
    }

    public function testPageSnippetCodeListIsReturned()
    {
        $this->assertInternalType('array', $this->pageMetaInfo->getPageSnippetCodes());
    }

    public function testThePageSnippetListIncludesTheProductJsonAndPriceSnippetCodes()
    {
        $pageSnippetCodes = $this->pageMetaInfo->getPageSnippetCodes();
        $this->assertContains(ProductJsonSnippetRenderer::CODE, $pageSnippetCodes);
        $this->assertContains(PriceSnippetRenderer::PRICE, $pageSnippetCodes);
        $this->assertContains(PriceSnippetRenderer::SPECIAL_PRICE, $pageSnippetCodes);
    }

    public function testItReturnsThePageSnippetContainers()
    {
        $this->assertSame($this->containers, $this->pageMetaInfo->getContainerSnippets());
    }

    public function testReturnsPageSpecificData()
    {
        $this->assertSame($this->pageSpecificData, $this->pageMetaInfo->getPageSpecificData());
    }
}
