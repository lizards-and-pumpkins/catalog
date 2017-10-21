<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductListing\ContentDelivery;

use LizardsAndPumpkins\Import\PageMetaInfoSnippetContent;
use LizardsAndPumpkins\Util\Exception\InvalidSnippetCodeException;
use PHPUnit\Framework\TestCase;

/**
 * @covers   \LizardsAndPumpkins\ProductListing\ContentDelivery\ProductSearchResultMetaSnippetContent
 * @covers   \LizardsAndPumpkins\Util\SnippetCodeValidator
 * @uses     \LizardsAndPumpkins\Import\SnippetContainer
 */
class ProductSearchResultMetaSnippetContentTest extends TestCase
{
    /**
     * @var ProductSearchResultMetaSnippetContent
     */
    private $metaSnippetContent;

    /**
     * @var string
     */
    private $dummyRootSnippetCode = 'foo';

    private $containerSnippets = ['additional-info' => []];

    private $pageSpecificData = [['foo' => 'bar'], ['baz' => 'qux']];

    protected function setUp()
    {
        $this->metaSnippetContent = ProductSearchResultMetaSnippetContent::create(
            $this->dummyRootSnippetCode,
            [$this->dummyRootSnippetCode],
            $this->containerSnippets,
            $this->pageSpecificData
        );
    }

    public function testPageMetaInfoSnippetContentInterfaceIsImplemented()
    {
        $this->assertInstanceOf(PageMetaInfoSnippetContent::class, $this->metaSnippetContent);
    }

    public function testExceptionIsThrownIfTheRootSnippetCodeIsAnEmptyString()
    {
        $this->expectException(InvalidSnippetCodeException::class);
        ProductSearchResultMetaSnippetContent::create('', [], [], []);
    }

    public function testMetaSnippetContentInfoContainsRequiredKeys()
    {
        $expectedKeys = [
            ProductSearchResultMetaSnippetContent::KEY_ROOT_SNIPPET_CODE,
            ProductSearchResultMetaSnippetContent::KEY_PAGE_SNIPPET_CODES,
            ProductSearchResultMetaSnippetContent::KEY_CONTAINER_SNIPPETS,
            ProductSearchResultMetaSnippetContent::KEY_PAGE_SPECIFIC_DATA,
        ];

        $result = $this->metaSnippetContent->toArray();

        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey($key, $result, sprintf('Page meta info array is lacking "%s" key', $key));
        }
    }

    public function testRootSnippetCodeIsReturned()
    {
        $this->assertEquals($this->dummyRootSnippetCode, $this->metaSnippetContent->getRootSnippetCode());
    }

    public function testPageSnippetCodeListIsReturned()
    {
        $this->assertInternalType('array', $this->metaSnippetContent->getPageSnippetCodes());
    }

    public function testRootSnippetCodeIsAddedToTheSnippetCodeListIfAbsent()
    {
        $metaSnippetContent = ProductSearchResultMetaSnippetContent::create($this->dummyRootSnippetCode, [], [], []);
        $metaMetaInfo = $metaSnippetContent->toArray();
        $pageSnippetCodes = $metaMetaInfo[ProductSearchResultMetaSnippetContent::KEY_PAGE_SNIPPET_CODES];

        $this->assertContains($this->dummyRootSnippetCode, $pageSnippetCodes);
    }

    public function testCanBeCreatedFromJson()
    {
        $metaSnippetContent = ProductSearchResultMetaSnippetContent::fromArray($this->metaSnippetContent->toArray());

        $this->assertInstanceOf(ProductSearchResultMetaSnippetContent::class, $metaSnippetContent);
    }

    /**
     * @dataProvider pageInfoArrayKeyProvider
     */
    public function testExceptionIsThrownIfJsonDoesNotContainRequiredData(string $missingKey)
    {
        $pageMeta = $this->metaSnippetContent->toArray();
        unset($pageMeta[$missingKey]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(sprintf('Missing "%s" in input array', $missingKey));

        ProductSearchResultMetaSnippetContent::fromArray($pageMeta);
    }

    /**
     * @return array[]
     */
    public function pageInfoArrayKeyProvider() : array
    {
        return [
            [ProductSearchResultMetaSnippetContent::KEY_ROOT_SNIPPET_CODE],
            [ProductSearchResultMetaSnippetContent::KEY_PAGE_SNIPPET_CODES],
            [ProductSearchResultMetaSnippetContent::KEY_CONTAINER_SNIPPETS],
            [ProductSearchResultMetaSnippetContent::KEY_PAGE_SPECIFIC_DATA],
        ];
    }

    public function testItReturnsThePageSnippetContainers()
    {
        $this->assertSame($this->containerSnippets, $this->metaSnippetContent->getContainerSnippets());
    }

    public function testReturnsPageSpecificData()
    {
        $this->assertSame($this->pageSpecificData, $this->metaSnippetContent->getPageSpecificData());
    }
}
