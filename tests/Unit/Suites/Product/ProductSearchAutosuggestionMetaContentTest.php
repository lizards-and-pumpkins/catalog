<?php

namespace Brera\Product;

use Brera\PageMetaInfoSnippetContent;

/**
 * @covers \Brera\Product\ProductSearchAutosuggestionMetaContent
 */
class ProductSearchAutosuggestionMetaContentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductSearchAutosuggestionMetaContent
     */
    private $metaSnippetContent;

    /**
     * @var string
     */
    private $dummyRootSnippetCode = 'foo';

    protected function setUp()
    {
        $this->metaSnippetContent = ProductSearchAutosuggestionMetaContent::create(
            $this->dummyRootSnippetCode,
            [$this->dummyRootSnippetCode]
        );
    }

    public function testPageMetaInfoSnippetContentInterfaceIsImplemented()
    {
        $this->assertInstanceOf(PageMetaInfoSnippetContent::class, $this->metaSnippetContent);
    }

    public function testExceptionIsThrownIfTheRootSnippetCodeIsNoString()
    {
        $this->setExpectedException(\InvalidArgumentException::class);
        ProductSearchAutosuggestionMetaContent::create(1, []);
    }

    public function testMetaSnippetContentInfoContainsRequiredKeys()
    {
        $expectedKeys = [
            ProductSearchAutosuggestionMetaContent::KEY_ROOT_SNIPPET_CODE,
            ProductSearchAutosuggestionMetaContent::KEY_PAGE_SNIPPET_CODES
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
        $metaSnippetContent = ProductSearchAutosuggestionMetaContent::create($this->dummyRootSnippetCode, []);
        $metaMetaInfo = $metaSnippetContent->getInfo();
        $pageSnippetCodes = $metaMetaInfo[ProductSearchAutosuggestionMetaContent::KEY_PAGE_SNIPPET_CODES];

        $this->assertContains($this->dummyRootSnippetCode, $pageSnippetCodes);
    }

    public function testCanBeCreatedFromJson()
    {
        $jsonEncodedPageMetaInfo = json_encode($this->metaSnippetContent->getInfo());
        $metaSnippetContent = ProductSearchAutosuggestionMetaContent::fromJson($jsonEncodedPageMetaInfo);

        $this->assertInstanceOf(ProductSearchAutosuggestionMetaContent::class, $metaSnippetContent);
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

        ProductSearchAutosuggestionMetaContent::fromJson(json_encode($pageMetaInfo));
    }

    /**
     * @return array[]
     */
    public function pageInfoArrayKeyProvider()
    {
        return [
            [ProductSearchAutosuggestionMetaContent::KEY_ROOT_SNIPPET_CODE],
            [ProductSearchAutosuggestionMetaContent::KEY_PAGE_SNIPPET_CODES],
        ];
    }

    public function testExceptionIsThrownInCaseOfJsonErrors()
    {
        $this->setExpectedException(\OutOfBoundsException::class);
        ProductSearchAutosuggestionMetaContent::fromJson('malformed-json');
    }
}
