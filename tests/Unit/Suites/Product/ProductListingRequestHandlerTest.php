<?php

namespace Brera\Product;

use Brera\Http\AbstractRequestHandlerTest;
use Brera\SnippetKeyGenerator;

/**
 * @covers \Brera\Product\ProductListingRequestHandler
 * @covers \Brera\Http\AbstractHttpRequestHandler
 * @uses   \Brera\Http\HttpUrl
 * @uses   \Brera\Page
 * @uses   \Brera\SnippetKeyGeneratorLocator
 * @uses   \Brera\Product\ProductListingMetaInfoSnippetContent
 * @uses   \Brera\GenericSnippetKeyGenerator
 * @uses   \Brera\MissingSnippetCodeMessage
 */
class ProductListingRequestHandlerTest extends AbstractRequestHandlerTest
{
    /**
     * @var string[]
     */
    private $testSelectionCriteria = ['test-attribute' => 'test-value'];

    /**
     * @return ProductListingRequestHandler
     */
    protected function createRequestHandlerInstance()
    {
        return new ProductListingRequestHandler(
            $this->getUrlPathKeyFixture(),
            $this->getStubContext(),
            $this->getSnippetKeyGeneratorLocator(),
            $this->getMockDataPoolReader(),
            $this->getStubLogger()
        );
    }

    /**
     * @param string $rootSnippetCode
     * @param string[] $allSnippetCodes
     * @return mixed[]
     */
    protected function buildStubPageMetaInfo($rootSnippetCode, array $allSnippetCodes)
    {
        $pageMetaInfo = [
            ProductListingMetaInfoSnippetContent::KEY_CRITERIA => $this->testSelectionCriteria,
            ProductDetailPageMetaInfoSnippetContent::KEY_ROOT_SNIPPET_CODE => $rootSnippetCode,
            ProductDetailPageMetaInfoSnippetContent::KEY_PAGE_SNIPPET_CODES => $allSnippetCodes
        ];
        return $pageMetaInfo;
    }
    
    /**
     * @return ProductSnippetKeyGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getKeyGeneratorMock()
    {
        return $this->getMock(SnippetKeyGenerator::class);
    }

    /**
     * @test
     */
    public function itShouldLoadThePageCriteriaProductsFromTheSearchEngine()
    {
        $this->setDataPoolFixture('root-snippet', 'no content', []);
        
        $this->getMockDataPoolReader()->expects($this->once())->method('getProductIdsMatchingCriteria')
            ->with($this->testSelectionCriteria)->willReturn([1]);
        
        $this->getRequestHandlerUnderTest()->process();
    }

    /**
     * @test
     */
    public function itShouldLoadTheProductSnippetsFromTheQueryResult()
    {
        $rootSnippetCode = 'root-snippet';
        $allSnippetCodesInPageExceptProductListing = [];
        $testMatchingProductIds = ['3'];
        $productInListingSnippetFixture = ['product_in_listing_3' => 'Product in Listing Content'];
        $this->setPageMetaInfoFixture($rootSnippetCode, $allSnippetCodesInPageExceptProductListing);
        $this->setMatchingProductsFixture($testMatchingProductIds);
        $this->setProductListSnippetsInKeyValueStorageFixture($rootSnippetCode, $productInListingSnippetFixture);
        
        $this->getRequestHandlerUnderTest()->process();
    }

    /**
     * @test
     */
    public function itShouldMapTheProductInListSnippetKeysToIncrementingSnippetCodes()
    {
        $rootSnippetCode = 'root-snippet';
        $allSnippetCodesInPageExceptProductListing = [];
        $testMatchingProductIds = ['id1', 'id2', 'id3'];
        $productInListingSnippetFixture = [
            'product_in_listing_id1' => 'Product 1 in Listing Snippet',
            'product_in_listing_id2' => 'Product 2 in Listing Snippet',
            'product_in_listing_id3' => 'Product 3 in Listing Snippet',
        ];
        $expectedSnippetCodeToKeyMap = [
            $rootSnippetCode => $rootSnippetCode,
            'product_1' => 'product_in_listing_id1',
            'product_2' => 'product_in_listing_id2',
            'product_3' => 'product_in_listing_id3',
        ];
        $this->setPageMetaInfoFixture($rootSnippetCode, $allSnippetCodesInPageExceptProductListing);
        $this->setMatchingProductsFixture($testMatchingProductIds);
        $this->setProductListSnippetsInKeyValueStorageFixture($rootSnippetCode, $productInListingSnippetFixture);

        $requestHandler = $this->getRequestHandlerUnderTest();
        $requestHandler->process();

        $this->assertAttributeEquals($expectedSnippetCodeToKeyMap, 'snippetCodesToKeyMap', $requestHandler);
    }

    /**
     * @param $testMatchingProductIds
     */
    private function setMatchingProductsFixture($testMatchingProductIds)
    {
        $this->getMockDataPoolReader()->expects($this->once())->method('getProductIdsMatchingCriteria')
            ->with($this->testSelectionCriteria)->willReturn($testMatchingProductIds);
    }

    /**
     * @param $rootSnippetCode
     * @param $productInListingSnippetFixture
     */
    private function setProductListSnippetsInKeyValueStorageFixture($rootSnippetCode, $productInListingSnippetFixture)
    {
        $this->getMockDataPoolReader()->expects($this->exactly(2))->method('getSnippets')
            ->willReturnMap([
                [[$rootSnippetCode], [$rootSnippetCode => 'dummy root snippet content']],
                [array_keys($productInListingSnippetFixture), $productInListingSnippetFixture],
            ]);
    }
}
