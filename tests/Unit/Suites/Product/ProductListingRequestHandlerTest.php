<?php

namespace Brera\Product;

/**
 * @covers \Brera\Product\ProductListingRequestHandler
 * @covers \Brera\AbstractHttpRequestHandler
 * @uses   \Brera\Http\HttpUrl
 * @uses   \Brera\Page
 * @uses   \Brera\SnippetKeyGeneratorLocator
 * @uses   \Brera\PageMetaInfoSnippetContent
 * @uses   \Brera\GenericSnippetKeyGenerator
 * @uses   \Brera\MissingSnippetCodeMessage
 */
class ProductListingRequestHandlerTest extends AbstractRequestHandlerTest
{
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
}
