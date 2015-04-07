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

    /**
     * @test
     */
    public function itShouldReturnArray()
    {
        $this->assertInternalType('array', $this->pageMetaInfo->getInfo());
    }

    /**
     * @test
     */
    public function itShouldContainTheExpectedArrayKeysInTheJsonContent()
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

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function itShouldThrowAnExceptionIfTheRootSnippetCodeIsNoString()
    {
        ProductListingMetaInfoSnippetContent::create([], 1.0, []);
    }

    /**
     * @test
     */
    public function itShouldAddTheRootSnippetCodeToTheSnippetCodeListIfNotPresent()
    {
        $rootSnippetCode = 'root-snippet-code';
        $pageMetaInfo = ProductListingMetaInfoSnippetContent::create([], $rootSnippetCode, []);
        $this->assertContains(
            $rootSnippetCode,
            $pageMetaInfo->getInfo()[ProductListingMetaInfoSnippetContent::KEY_PAGE_SNIPPET_CODES]
        );
    }

    /**
     * @test
     */
    public function itShouldHaveAFromJsonConstructor()
    {
        $pageMetaInfo = ProductListingMetaInfoSnippetContent::fromJson(json_encode($this->pageMetaInfo->getInfo()));
        $this->assertInstanceOf(ProductListingMetaInfoSnippetContent::class, $pageMetaInfo);
    }
    
    /**
     * @test
     * @expectedException \OutOfBoundsException
     */
    public function itShouldThrowAnExceptionInCaseOfJsonErrors()
    {
        ProductListingMetaInfoSnippetContent::fromJson('malformed-json');
    }

    /**
     * @test
     * @dataProvider pageInfoArrayKeyProvider
     * @param string $key
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Missing key in input JSON
     */
    public function itShouldThrowAnExceptionIfARequiredKeyIsMissing($key)
    {
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

    /**
     * @test
     */
    public function itShouldReturnTheSelectionCriteria()
    {
        $this->assertEquals($this->selectionCriteria, $this->pageMetaInfo->getSelectionCriteria());
    }

    /**
     * @test
     */
    public function itShouldReturnTheRootSnippetCode()
    {
        $this->assertEquals($this->rootSnippetCode, $this->pageMetaInfo->getRootSnippetCode());
    }

    /**
     * @test
     */
    public function itShouldReturnThePageSnippetCodeList()
    {
        $this->assertInternalType('array', $this->pageMetaInfo->getPageSnippetCodes());
    }
}
