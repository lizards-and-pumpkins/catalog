<?php

namespace Brera\Product;

/**
 * @covers \Brera\Product\ProductDetailPageMetaInfoSnippetContent
 */
class ProductDetailPageMetaInfoSnippetContentTest extends \PHPUnit_Framework_TestCase
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

    protected function setUp()
    {
        $this->pageMetaInfo = ProductDetailPageMetaInfoSnippetContent::create(
            $this->sourceId,
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
            ProductDetailPageMetaInfoSnippetContent::KEY_PRODUCT_ID,
            ProductDetailPageMetaInfoSnippetContent::KEY_ROOT_SNIPPET_CODE,
            ProductDetailPageMetaInfoSnippetContent::KEY_PAGE_SNIPPET_CODES
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
    public function itShouldThrowAnExceptionIfTheSourceIdIsNotScalar()
    {
        ProductDetailPageMetaInfoSnippetContent::create([], 'test', []);
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function itShouldThrowAnExceptionIfTheRootSnippetCodeIsNoString()
    {
        ProductDetailPageMetaInfoSnippetContent::create(123, 1.0, []);
    }

    /**
     * @test
     */
    public function itShouldAddTheRootSnippetCodeToTheSnippetCodeListIfNotPresent()
    {
        $rootSnippetCode = 'root-snippet-code';
        $pageMetaInfo = ProductDetailPageMetaInfoSnippetContent::create('123', $rootSnippetCode, []);
        $this->assertContains(
            $rootSnippetCode,
            $pageMetaInfo->getInfo()[ProductDetailPageMetaInfoSnippetContent::KEY_PAGE_SNIPPET_CODES]
        );
    }

    /**
     * @test
     */
    public function itShouldHaveAFromJsonConstructor()
    {
        $pageMetaInfo = ProductDetailPageMetaInfoSnippetContent::fromJson(json_encode($this->pageMetaInfo->getInfo()));
        $this->assertInstanceOf(ProductDetailPageMetaInfoSnippetContent::class, $pageMetaInfo);
    }
    
    /**
     * @test
     * @expectedException \OutOfBoundsException
     */
    public function itShouldThrowAnExceptionInCaseOfJsonErrors()
    {
        ProductDetailPageMetaInfoSnippetContent::fromJson('malformed-json');
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
        ProductDetailPageMetaInfoSnippetContent::fromJson(json_encode($pageInfo));
    }

    /**
     * @return array[]
     */
    public function pageInfoArrayKeyProvider()
    {
        return [
            [ProductDetailPageMetaInfoSnippetContent::KEY_PRODUCT_ID],
            [ProductDetailPageMetaInfoSnippetContent::KEY_ROOT_SNIPPET_CODE],
            [ProductDetailPageMetaInfoSnippetContent::KEY_PAGE_SNIPPET_CODES],
        ];
    }

    /**
     * @test
     */
    public function itShouldReturnTheSourceId()
    {
        $this->assertEquals($this->sourceId, $this->pageMetaInfo->getProductId());
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
