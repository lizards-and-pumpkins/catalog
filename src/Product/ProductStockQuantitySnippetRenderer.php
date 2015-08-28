<?php

namespace Brera\Product;

use Brera\Context\ContextBuilder;
use Brera\Snippet;
use Brera\SnippetKeyGenerator;
use Brera\SnippetList;
use Brera\SnippetRenderer;

class ProductStockQuantitySnippetRenderer implements SnippetRenderer
{
    const CODE = 'product_stock_quantity';

    /**
     * @var SnippetKeyGenerator
     */
    private $snippetKeyGenerator;

    /**
     * @var ContextBuilder
     */
    private $contextBuilder;

    /**
     * @var SnippetList
     */
    private $snippetList;

    public function __construct(
        SnippetKeyGenerator $snippetKeyGenerator,
        ContextBuilder $contextBuilder,
        SnippetList $snippetList
    ) {
        $this->snippetKeyGenerator = $snippetKeyGenerator;
        $this->contextBuilder = $contextBuilder;
        $this->snippetList = $snippetList;
    }

    /**
     * @param ProductStockQuantitySource $productStockQuantitySource
     * @return SnippetList
     */
    public function render(ProductStockQuantitySource $productStockQuantitySource)
    {
        $key = $this->getSnippetKey($productStockQuantitySource);
        $content = $productStockQuantitySource->getStock()->getQuantity();
        $snippet = Snippet::create($key, $content);
        $this->snippetList->add($snippet);

        return $this->snippetList;
    }

    /**
     * @param ProductStockQuantitySource $productStockQuantitySource
     * @return string
     */
    private function getSnippetKey(ProductStockQuantitySource $productStockQuantitySource)
    {
        $productId = $productStockQuantitySource->getProductId();
        $contextData = $productStockQuantitySource->getContextData();
        $context = $this->contextBuilder->createContext($contextData);

        $key = $this->snippetKeyGenerator->getKeyForContext($context, ['product_id' => $productId]);

        return $key;
    }
}
