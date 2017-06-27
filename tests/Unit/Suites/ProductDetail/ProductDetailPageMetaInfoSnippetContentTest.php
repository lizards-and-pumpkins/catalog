<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductDetail;

use LizardsAndPumpkins\Import\Product\ProductJsonSnippetRenderer;
use LizardsAndPumpkins\Import\SnippetCode;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\ProductDetail\ProductDetailPageMetaInfoSnippetContent
 * @uses   \LizardsAndPumpkins\Import\SnippetContainer
 * @uses   \LizardsAndPumpkins\Import\SnippetCode
 */
class ProductDetailPageMetaInfoSnippetContentTest extends TestCase
{
    /**
     * @var ProductDetailPageMetaInfoSnippetContent
     */
    private $pageMetaInfo;

    /**
     * @var SnippetCode
     */
    private $rootSnippetCode;

    /**
     * @var string
     */
    private $sourceId = '123';

    private $containers = ['additional_info' => []];

    protected function setUp()
    {
        $this->rootSnippetCode = new SnippetCode('root-snippet-code');

        $this->pageMetaInfo = ProductDetailPageMetaInfoSnippetContent::create(
            $this->sourceId,
            $this->rootSnippetCode,
            [$this->rootSnippetCode],
            $this->containers
        );
    }

    public function testPageMetaInfoIsAnArray()
    {
        $this->assertInternalType('array', $this->pageMetaInfo->getInfo());
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
                array_key_exists($key, $this->pageMetaInfo->getInfo()),
                sprintf('The expected key "%s" is not set on the page meta info array', $key)
            );
        }
    }

    public function testExceptionIsThrownIfTheSourceIdIsNotScalar()
    {
        $this->expectException(\TypeError::class);
        ProductDetailPageMetaInfoSnippetContent::create([], new SnippetCode('test'), [], []);
    }

    public function testRootSnippetCodeIsAddedToSnippetCodeListIfNotPresent()
    {
        $pageMetaInfo = ProductDetailPageMetaInfoSnippetContent::create('123', $this->rootSnippetCode, [], []);

        $this->assertContains($this->rootSnippetCode, $pageMetaInfo->getPageSnippetCodes());
    }

    public function testFromJsonConstructorIsPresent()
    {
        $pageMetaInfo = ProductDetailPageMetaInfoSnippetContent::fromJson(json_encode($this->pageMetaInfo->getInfo()));
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
        $pageInfo = $this->pageMetaInfo->getInfo();
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
    }

    public function testItReturnsThePageSnippetContainers()
    {
        $this->assertSame($this->containers, $this->pageMetaInfo->getContainerSnippets());
    }
}
