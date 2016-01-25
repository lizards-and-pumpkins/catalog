<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\PageMetaInfoSnippetContent;

/**
 * @covers \LizardsAndPumpkins\Product\ProductSearchResultMetaSnippetContent
 * @uses   \LizardsAndPumpkins\SnippetContainer
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

    private $containerSnippets = ['additonal-info' => []];

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

    public function testExceptionIsThrownIfTheRootSnippetCodeIsNoString()
    {
        $this->setExpectedException(\InvalidArgumentException::class);
        ProductSearchResultMetaSnippetContent::create(1, [], []);
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
     * @param string $missingKey
     */
    public function testExceptionIsThrownIfJsonDoesNotContainRequiredData($missingKey)
    {
        $pageMetaInfo = $this->metaSnippetContent->getInfo();
        unset($pageMetaInfo[$missingKey]);

        $this->setExpectedException(\RuntimeException::class, sprintf('Missing "%s" in input JSON', $missingKey));

        ProductSearchResultMetaSnippetContent::fromJson(json_encode($pageMetaInfo));
    }

    /**
     * @return array[]
     */
    public function pageInfoArrayKeyProvider()
    {
        return [
            [ProductSearchResultMetaSnippetContent::KEY_ROOT_SNIPPET_CODE],
            [ProductSearchResultMetaSnippetContent::KEY_PAGE_SNIPPET_CODES],
            [ProductSearchResultMetaSnippetContent::KEY_CONTAINER_SNIPPETS],
        ];
    }

    public function testExceptionIsThrownInCaseOfJsonErrors()
    {
        $this->setExpectedException(\OutOfBoundsException::class);
        ProductSearchResultMetaSnippetContent::fromJson('malformed-json');
    }

    public function testItReturnsThePageSnippetContainers()
    {
        $this->assertSame($this->containerSnippets, $this->metaSnippetContent->getContainerSnippets());
    }
}
