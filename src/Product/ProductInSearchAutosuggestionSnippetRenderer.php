<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\ContextSource;
use LizardsAndPumpkins\InvalidProjectionSourceDataTypeException;
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
     * @param mixed $projectionSourceData
     * @return SnippetList
     */
    public function render($projectionSourceData)
    {
        if (!($projectionSourceData instanceof Product)) {
            throw new InvalidProjectionSourceDataTypeException('First argument must be a Product instance.');
        }

        $this->addProductInSearchAutosuggestionSnippetsToList($projectionSourceData);

        return $this->snippetList;
    }

    private function addProductInSearchAutosuggestionSnippetsToList(Product $product)
    {
        $content = $this->blockRenderer->render($product, $product->getContext());
        $key = $this->snippetKeyGenerator->getKeyForContext($product->getContext(), [Product::ID => $product->getId()]);
        $contentSnippet = Snippet::create($key, $content);
        $this->snippetList->add($contentSnippet);
    }
}
