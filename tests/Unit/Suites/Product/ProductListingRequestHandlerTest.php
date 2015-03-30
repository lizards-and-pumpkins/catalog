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
     * @return ProductDetailSnippetKeyGenerator|\PHPUnit_Framework_MockObject_MockObject
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
        $this->setPageMetaInfoFixture($rootSnippetCode, []);

        $this->getMockDataPoolReader()->expects($this->any())->method('getProductIdsMatchingCriteria')
            ->with($this->testSelectionCriteria)->willReturn([1]);

        $productInListingSnippetKey = 'product_in_listing_1';
        $this->getMockDataPoolReader()->expects($this->exactly(2))->method('getSnippets')
            ->willReturnMap([
                [[$rootSnippetCode], [$rootSnippetCode => 'dummy root snippet content']],
                [[$productInListingSnippetKey], [$productInListingSnippetKey => 'Product in Listing Content']],
            ]);
        
        $this->getRequestHandlerUnderTest()->process();
    }
}
