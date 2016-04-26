<?php

namespace LizardsAndPumpkins\ProductSearch\Import;

use LizardsAndPumpkins\Import\PageMetaInfoSnippetContent;
use LizardsAndPumpkins\Util\Exception\InvalidSnippetCodeException;

/**
 * @covers   \LizardsAndPumpkins\ProductSearch\Import\ProductSearchAutosuggestionMetaSnippetContent
 * @covers   \LizardsAndPumpkins\Util\SnippetCodeValidator
 * @uses     \LizardsAndPumpkins\Import\SnippetContainer
 */
class ProductSearchAutosuggestionMetaSnippetContentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductSearchAutosuggestionMetaSnippetContent
     */
    private $metaSnippetContent;

    /**
     * @var string
     */
    private $dummyRootSnippetCode = 'foo';

    private $containerSnippetData = ['some-container' => []];

    protected function setUp()
    {
        $this->metaSnippetContent = ProductSearchAutosuggestionMetaSnippetContent::create(
            $this->dummyRootSnippetCode,
            [$this->dummyRootSnippetCode],
            $this->containerSnippetData
        );
    }

    public function testPageMetaInfoSnippetContentInterfaceIsImplemented()
    {
        $this->assertInstanceOf(PageMetaInfoSnippetContent::class, $this->metaSnippetContent);
    }

    public function testExceptionIsThrownIfTheRootSnippetCodeIsNoString()
    {
        $this->expectException(InvalidSnippetCodeException::class);
        ProductSearchAutosuggestionMetaSnippetContent::create(1, [], []);
    }

    public function testMetaSnippetContentInfoContainsRequiredKeys()
    {
        $expectedKeys = [
            ProductSearchAutosuggestionMetaSnippetContent::KEY_ROOT_SNIPPET_CODE,
            ProductSearchAutosuggestionMetaSnippetContent::KEY_PAGE_SNIPPET_CODES,
            ProductSearchAutosuggestionMetaSnippetContent::KEY_CONTAINER_SNIPPETS,
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

    public function testPageSnippetCodesArrayIsReturned()
    {
        $this->assertSame([$this->dummyRootSnippetCode], $this->metaSnippetContent->getPageSnippetCodes());
    }

    public function testRootSnippetCodeIsAddedToTheSnippetCodeListIfAbsent()
    {
        $metaSnippetContent = ProductSearchAutosuggestionMetaSnippetContent::create(
            $this->dummyRootSnippetCode,
            [],
            $this->containerSnippetData
        );
        $metaMetaInfo = $metaSnippetContent->getInfo();
        $pageSnippetCodes = $metaMetaInfo[ProductSearchAutosuggestionMetaSnippetContent::KEY_PAGE_SNIPPET_CODES];

        $this->assertContains($this->dummyRootSnippetCode, $pageSnippetCodes);
    }

    public function testCanBeCreatedFromJson()
    {
        $jsonEncodedPageMetaInfo = json_encode($this->metaSnippetContent->getInfo());
        $metaSnippetContent = ProductSearchAutosuggestionMetaSnippetContent::fromJson($jsonEncodedPageMetaInfo);

        $this->assertInstanceOf(ProductSearchAutosuggestionMetaSnippetContent::class, $metaSnippetContent);
    }

    /**
     * @dataProvider pageInfoArrayKeyProvider
     * @param string $missingKey
     */
    public function testExceptionIsThrownIfJsonDoesNotContainRequiredData($missingKey)
    {
        $pageMetaInfo = $this->metaSnippetContent->getInfo();
        unset($pageMetaInfo[$missingKey]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(sprintf('Missing "%s" in input JSON', $missingKey));

        ProductSearchAutosuggestionMetaSnippetContent::fromJson(json_encode($pageMetaInfo));
    }

    /**
     * @return array[]
     */
    public function pageInfoArrayKeyProvider()
    {
        return [
            [ProductSearchAutosuggestionMetaSnippetContent::KEY_ROOT_SNIPPET_CODE],
            [ProductSearchAutosuggestionMetaSnippetContent::KEY_PAGE_SNIPPET_CODES],
            [ProductSearchAutosuggestionMetaSnippetContent::KEY_CONTAINER_SNIPPETS],
        ];
    }

    public function testExceptionIsThrownInCaseOfJsonErrors()
    {
        $this->expectException(\OutOfBoundsException::class);
        ProductSearchAutosuggestionMetaSnippetContent::fromJson('malformed-json');
    }

    public function testItReturnsThePageSnippetContainers()
    {
        $this->assertSame($this->containerSnippetData, $this->metaSnippetContent->getContainerSnippets());
    }
}
