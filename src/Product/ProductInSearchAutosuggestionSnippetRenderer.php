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
     * @var SnippetList
     */
    private $snippetList;

    /**
     * @var ProductInSearchAutosuggestionBlockRenderer
     */
    private $blockRenderer;

    /**
     * @var SnippetKeyGenerator
     */
    private $snippetKeyGenerator;

    public function __construct(
        SnippetList $snippetList,
        ProductInSearchAutosuggestionBlockRenderer $blockRenderer,
        SnippetKeyGenerator $snippetKeyGenerator
    ) {
        $this->snippetList = $snippetList;
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

        $this->addProductInSearchAutosuggestionSnippetsToList($projectionSourceData);

        return $this->snippetList;
    }

    private function addProductInSearchAutosuggestionSnippetsToList(ProductView $product)
    {
        $content = $this->blockRenderer->render($product, $product->getContext());
        $key = $this->snippetKeyGenerator->getKeyForContext($product->getContext(), [Product::ID => $product->getId()]);
        $contentSnippet = Snippet::create($key, $content);
        $this->snippetList->add($contentSnippet);
    }
}
