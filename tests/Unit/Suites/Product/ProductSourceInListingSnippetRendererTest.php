<?php

namespace Brera\Product;

/**
 * @covers \Brera\Product\ProductSourceInListingSnippetRenderer
 */
class ProductSourceInListingSnippetRendererTest extends AbstractProductSnippetRendererTest
{
    protected function setUp()
    {
        $this->initMockContextSource();
        $this->initMockSnippetResultList();

        $this->snippetRenderer = new ProductSourceInListingSnippetRenderer(
            $this->mockSnippetResultList,
            $this->getProductInContextRendererMock(ProductInListingInContextSnippetRenderer::class)
        );
    }
}
