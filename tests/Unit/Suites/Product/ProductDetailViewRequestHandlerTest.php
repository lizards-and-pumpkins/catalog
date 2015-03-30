<?php

namespace Brera\Product;

use Brera\Http\AbstractRequestHandlerTest;

/**
 * @covers \Brera\Product\ProductDetailViewRequestHandler
 * @covers \Brera\Http\AbstractHttpRequestHandler
 * @uses   \Brera\Product\ProductDetailPageMetaInfoSnippetContent
 * @uses   \Brera\Http\HttpUrl
 * @uses   \Brera\Page
 * @uses   \Brera\SnippetKeyGeneratorLocator
 * @uses   \Brera\GenericSnippetKeyGenerator
 * @uses   \Brera\MissingSnippetCodeMessage
 */
class ProductDetailViewRequestHandlerTest extends AbstractRequestHandlerTest
{
    /**
     * @return ProductDetailViewRequestHandler
     */
    protected function createRequestHandlerInstance()
    {
        return new ProductDetailViewRequestHandler(
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
            ProductDetailPageMetaInfoSnippetContent::KEY_PRODUCT_ID => 'dummy-product-id',
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
        return $this->getMock(ProductDetailSnippetKeyGenerator::class);
    }
}
