<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductListing\ContentDelivery;

use LizardsAndPumpkins\Import\PageMetaInfoSnippetContent;
use LizardsAndPumpkins\Util\Exception\InvalidSnippetCodeException;
use LizardsAndPumpkins\Import\SnippetCode;
use PHPUnit\Framework\TestCase;

/**
 * @covers   \LizardsAndPumpkins\ProductListing\ContentDelivery\ProductSearchResultMetaSnippetContent
 * @uses   \LizardsAndPumpkins\Import\SnippetCode
 * @uses     \LizardsAndPumpkins\Import\SnippetContainer
 */
class ProductSearchResultMetaSnippetContentTest extends TestCase
{
    /**
     * @var ProductSearchResultMetaSnippetContent
     */
    private $metaSnippetContent;

    /**
     * @var SnippetCode
     */
    private $rootSnippetCode;

    private $containerSnippets = ['additional-info' => []];

    protected function setUp()
    {
        $this->rootSnippetCode = new SnippetCode('root-snippet-code');

        $this->metaSnippetContent = ProductSearchResultMetaSnippetContent::create(
            $this->rootSnippetCode,
            [$this->rootSnippetCode],
            $this->containerSnippets
        );
    }

    public function testPageMetaInfoSnippetContentInterfaceIsImplemented()
    {
        $this->assertInstanceOf(PageMetaInfoSnippetContent::class, $this->metaSnippetContent);
    }

    public function testMetaSnippetContentInfoContainsRequiredKeys()
    {
        $expectedKeys = [
            ProductSearchResultMetaSnippetContent::KEY_ROOT_SNIPPET_CODE,
            ProductSearchResultMetaSnippetContent::KEY_PAGE_SNIPPET_CODES,
            ProductSearchResultMetaSnippetContent::KEY_CONTAINER_SNIPPETS,
        ];

        $result = $this->metaSnippetContent->getInfo();

        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey($key, $result, sprintf('Page meta info array is lacking "%s" key', $key));
        }
    }

    public function testRootSnippetCodeIsReturned()
    {
        $this->assertEquals($this->rootSnippetCode, $this->metaSnippetContent->getRootSnippetCode());
    }

    public function testPageSnippetCodeListIsReturned()
    {
        $this->assertInternalType('array', $this->metaSnippetContent->getPageSnippetCodes());
    }

    public function testRootSnippetCodeIsAddedToTheSnippetCodeListIfAbsent()
    {
        $metaSnippetContent = ProductSearchResultMetaSnippetContent::create($this->rootSnippetCode, [], []);
        $metaMetaInfo = $metaSnippetContent->getInfo();
        $pageSnippetCodes = $metaMetaInfo[ProductSearchResultMetaSnippetContent::KEY_PAGE_SNIPPET_CODES];

        $this->assertContains($this->rootSnippetCode, $pageSnippetCodes);
    }

    public function testCanBeCreatedFromJson()
    {
        $jsonEncodedPageMetaInfo = json_encode($this->metaSnippetContent->getInfo());
        $metaSnippetContent = ProductSearchResultMetaSnippetContent::fromJson($jsonEncodedPageMetaInfo);
        $this->assertInstanceOf(ProductSearchResultMetaSnippetContent::class, $metaSnippetContent);
    }

    /**
     * @dataProvider pageInfoArrayKeyProvider
     */
    public function testExceptionIsThrownIfJsonDoesNotContainRequiredData(string $missingKey)
    {
        $pageMetaInfo = $this->metaSnippetContent->getInfo();
        unset($pageMetaInfo[$missingKey]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(sprintf('Missing "%s" in input JSON', $missingKey));

        ProductSearchResultMetaSnippetContent::fromJson(json_encode($pageMetaInfo));
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
        ];
    }

    public function testExceptionIsThrownInCaseOfJsonErrors()
    {
        $this->expectException(\OutOfBoundsException::class);
        ProductSearchResultMetaSnippetContent::fromJson('malformed-json');
    }

    public function testItReturnsThePageSnippetContainers()
    {
        $this->assertSame($this->containerSnippets, $this->metaSnippetContent->getContainerSnippets());
    }
}
