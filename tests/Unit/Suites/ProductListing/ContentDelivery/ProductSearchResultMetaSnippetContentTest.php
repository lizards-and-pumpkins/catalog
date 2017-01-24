<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductListing\ContentDelivery;

use LizardsAndPumpkins\Import\PageMetaInfoSnippetContent;
use LizardsAndPumpkins\Util\Exception\InvalidSnippetCodeException;

/**
 * @covers   \LizardsAndPumpkins\ProductListing\ContentDelivery\ProductSearchResultMetaSnippetContent
 * @covers   \LizardsAndPumpkins\Util\SnippetCodeValidator
 * @uses     \LizardsAndPumpkins\Import\SnippetContainer
 */
class ProductSearchResultMetaSnippetContentTest extends \PHPUnit_Framework_TestCase
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

    protected function setUp()
    {
        $this->metaSnippetContent = ProductSearchResultMetaSnippetContent::create(
            $this->dummyRootSnippetCode,
            [$this->dummyRootSnippetCode],
            $this->containerSnippets
        );
    }

    public function testPageMetaInfoSnippetContentInterfaceIsImplemented()
    {
        $this->assertInstanceOf(PageMetaInfoSnippetContent::class, $this->metaSnippetContent);
    }

    public function testExceptionIsThrownIfTheRootSnippetCodeIsAnEmptyString()
    {
        $this->expectException(InvalidSnippetCodeException::class);
        ProductSearchResultMetaSnippetContent::create('', [], []);
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
        $this->assertEquals($this->dummyRootSnippetCode, $this->metaSnippetContent->getRootSnippetCode());
    }

    public function testPageSnippetCodeListIsReturned()
    {
        $this->assertInternalType('array', $this->metaSnippetContent->getPageSnippetCodes());
    }

    public function testRootSnippetCodeIsAddedToTheSnippetCodeListIfAbsent()
    {
        $metaSnippetContent = ProductSearchResultMetaSnippetContent::create($this->dummyRootSnippetCode, [], []);
        $metaMetaInfo = $metaSnippetContent->getInfo();
        $pageSnippetCodes = $metaMetaInfo[ProductSearchResultMetaSnippetContent::KEY_PAGE_SNIPPET_CODES];

        $this->assertContains($this->dummyRootSnippetCode, $pageSnippetCodes);
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
