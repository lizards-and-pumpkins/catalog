<?php

namespace Brera\Product;

/**
 * @covers \Brera\Product\ProductSourceInListingSnippetRenderer
 */
class ProductSourceInListingSnippetRendererTest extends AbstractProductSnippetRendererTest
{
    /**
     * @return ProductSourceInListingSnippetRenderer
     */
    protected function createSnippetRendererUnderTest()
    {
        return new ProductSourceInListingSnippetRenderer(
            $this->getMockSnippetResultList(),
            $this->getProductInContextRendererMock(ProductInListingInContextSnippetRenderer::class)
        );
    }
}
