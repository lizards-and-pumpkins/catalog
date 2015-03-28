<?php

namespace Brera\Product;

/**
 * @covers \Brera\Product\ProductDetailViewRequestHandler
 * @covers \Brera\AbstractHttpRequestHandler
 * @uses   \Brera\Http\HttpUrl
 * @uses   \Brera\Page
 * @uses   \Brera\SnippetKeyGeneratorLocator
 * @uses   \Brera\PageMetaInfoSnippetContent
 * @uses   \Brera\GenericSnippetKeyGenerator
 * @uses   \Brera\MissingSnippetCodeMessage
 */
class ProductDetailViewRequestHandlerTest extends AbstractRequestHandlerTest
{
    protected function createRequestHandlerInstance()
    {
        return new ProductDetailViewRequestHandler(
            $this->url,
            $this->stubContext,
            $this->mockUrlPathKeyGenerator,
            $this->snippetKeyGeneratorLocator,
            $this->mockDataPoolReader,
            $this->stubLogger
        );
    }
}
