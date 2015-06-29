<?php

namespace Brera\Product;

/**
 * @covers \Brera\Product\ProductListingMetaInfoSnippetContent
 */
class ProductListingMetaInfoSnippetContentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductListingMetaInfoSnippetContent
     */
    private $pageMetaInfo;

    /**
     * @var string
     */
    private $rootSnippetCode = 'root-snippet-code';

    /**
     * @var string
     */
    private $selectionCriteria = ['test-attribute' => 'test-value'];

    protected function setUp()
    {
        $this->pageMetaInfo = ProductListingMetaInfoSnippetContent::create(
            $this->selectionCriteria,
            $this->rootSnippetCode,
            [$this->rootSnippetCode]
        );
    }

    public function testArrayIsReturned()
    {
        $this->assertInternalType('array', $this->pageMetaInfo->getInfo());
    }

    public function testExpectedArrayKeysArePresentInJsonContent()
    {
        $keys = [
            ProductListingMetaInfoSnippetContent::KEY_CRITERIA,
            ProductListingMetaInfoSnippetContent::KEY_ROOT_SNIPPET_CODE,
            ProductListingMetaInfoSnippetContent::KEY_PAGE_SNIPPET_CODES
        ];
        foreach ($keys as $key) {
            $this->assertTrue(
                array_key_exists($key, $this->pageMetaInfo->getInfo()),
                sprintf('The expected key "%s" is not set on the page meta info array', $key)
            );
        }
    }

    public function testExceptionIsThrownIfTheRootSnippetCodeIsNoString()
    {
        $this->setExpectedException(\InvalidArgumentException::class);
        ProductListingMetaInfoSnippetContent::create([], 1.0, []);
    }

    public function testRootSnippetCodeIsAddedToTheSnippetCodeListIfNotPresent()
    {
        $rootSnippetCode = 'root-snippet-code';
        $pageMetaInfo = ProductListingMetaInfoSnippetContent::create([], $rootSnippetCode, []);
        $this->assertContains(
            $rootSnippetCode,
            $pageMetaInfo->getInfo()[ProductListingMetaInfoSnippetContent::KEY_PAGE_SNIPPET_CODES]
        );
    }

    public function testJsonConstructorIsPresent()
    {
        $pageMetaInfo = ProductListingMetaInfoSnippetContent::fromJson(json_encode($this->pageMetaInfo->getInfo()));
        $this->assertInstanceOf(ProductListingMetaInfoSnippetContent::class, $pageMetaInfo);
    }
    
    public function testExceptionIsThrownInCaseOfJsonErrors()
    {
        $this->setExpectedException(\OutOfBoundsException::class);
        ProductListingMetaInfoSnippetContent::fromJson('malformed-json');
    }

    /**
     * @dataProvider pageInfoArrayKeyProvider
     * @param string $key
     */
    public function testExceptionIsThrownIfARequiredKeyIsMissing($key)
    {
        $this->setExpectedException(\RuntimeException::class, 'Missing key in input JSON');
        $pageInfo = $this->pageMetaInfo->getInfo();
        unset($pageInfo[$key]);
        ProductListingMetaInfoSnippetContent::fromJson(json_encode($pageInfo));
    }

    /**
     * @return array[]
     */
    public function pageInfoArrayKeyProvider()
    {
        return [
            [ProductListingMetaInfoSnippetContent::KEY_CRITERIA],
            [ProductDetailPageMetaInfoSnippetContent::KEY_ROOT_SNIPPET_CODE],
            [ProductDetailPageMetaInfoSnippetContent::KEY_PAGE_SNIPPET_CODES],
        ];
    }

    public function testSelectionCriteriaIsReturned()
    {
        $this->assertEquals($this->selectionCriteria, $this->pageMetaInfo->getSelectionCriteria());
    }

    public function testRootSnippetCodeIsReturned()
    {
        $this->assertEquals($this->rootSnippetCode, $this->pageMetaInfo->getRootSnippetCode());
    }

    public function testPageSnippetCodeListIsReturned()
    {
        $this->assertInternalType('array', $this->pageMetaInfo->getPageSnippetCodes());
    }
}
