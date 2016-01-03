<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Exception\InvalidProjectionSourceDataTypeException;
use LizardsAndPumpkins\Projection\Catalog\ProductView;
use LizardsAndPumpkins\Snippet;
use LizardsAndPumpkins\SnippetKeyGenerator;
use LizardsAndPumpkins\SnippetList;
use LizardsAndPumpkins\SnippetRenderer;

class ProductInSearchAutosuggestionSnippetRenderer implements SnippetRenderer
{
    const CODE = 'product_in_search_autosuggestion';

    /**
     * @var ProductInSearchAutosuggestionBlockRenderer
     */
    private $blockRenderer;

    /**
     * @var SnippetKeyGenerator
     */
    private $snippetKeyGenerator;

    public function __construct(
        ProductInSearchAutosuggestionBlockRenderer $blockRenderer,
        SnippetKeyGenerator $snippetKeyGenerator
    ) {
        $this->blockRenderer = $blockRenderer;
        $this->snippetKeyGenerator = $snippetKeyGenerator;
    }

    /**
     * @param ProductView $projectionSourceData
     * @return SnippetList
     */
    public function render($projectionSourceData)
    {
        if (!($projectionSourceData instanceof ProductView)) {
            throw new InvalidProjectionSourceDataTypeException('First argument must be a ProductView instance.');
        }

        $snippet = $this->createProductInSearchAutosuggestionSnippet($projectionSourceData);

        return new SnippetList($snippet);
    }

    private function createProductInSearchAutosuggestionSnippet(ProductView $product)
    {
        $content = $this->blockRenderer->render($product, $product->getContext());
        $key = $this->snippetKeyGenerator->getKeyForContext($product->getContext(), [Product::ID => $product->getId()]);

        return Snippet::create($key, $content);
    }
}
