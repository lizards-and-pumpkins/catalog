<?php

namespace Brera\Product;

/**
 * @covers \Brera\Product\ProductSourceDetailViewSnippetRenderer
 */
class ProductSourceDetailViewSnippetRendererTest extends AbstractProductSnippetRendererTest
{
    protected function setUp()
    {
        $this->initMockContextSource();
        $this->initMockSnippetResultList();

        $this->snippetRenderer = new ProductSourceDetailViewSnippetRenderer(
            $this->mockSnippetResultList,
            $this->getProductInContextRendererMock(ProductInContextDetailViewSnippetRenderer::class)
        );
    }
}
