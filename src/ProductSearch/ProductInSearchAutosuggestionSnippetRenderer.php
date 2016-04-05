<?php

namespace LizardsAndPumpkins\ProductSearch;

use LizardsAndPumpkins\Import\Exception\InvalidProjectionSourceDataTypeException;
use LizardsAndPumpkins\Import\Product\Product;
use LizardsAndPumpkins\Import\Product\View\ProductView;
use LizardsAndPumpkins\DataPool\KeyValueStore\Snippet;
use LizardsAndPumpkins\DataPool\KeyGenerator\SnippetKeyGenerator;
use LizardsAndPumpkins\Import\SnippetRenderer;
use LizardsAndPumpkins\ProductSearch\TemplateRendering\ProductInSearchAutosuggestionBlockRenderer;

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
     * @return Snippet
     */
    public function render($projectionSourceData)
    {
        if (!($projectionSourceData instanceof ProductView)) {
            throw new InvalidProjectionSourceDataTypeException('First argument must be a ProductView instance.');
        }

        return [
            $this->createProductInSearchAutosuggestionSnippet($projectionSourceData)
        ];
    }

    /**
     * @param ProductView $product
     * @return Snippet
     */
    private function createProductInSearchAutosuggestionSnippet(ProductView $product)
    {
        $content = $this->blockRenderer->render($product, $product->getContext());
        $key = $this->snippetKeyGenerator->getKeyForContext($product->getContext(), [Product::ID => $product->getId()]);

        return Snippet::create($key, $content);
    }
}
