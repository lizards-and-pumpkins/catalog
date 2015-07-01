<?php

namespace Brera\Product;

use Brera\Snippet;
use Brera\SnippetKeyGenerator;
use Brera\SnippetList;
use Brera\SnippetRenderer;

class ProductStockQuantitySnippetRenderer implements SnippetRenderer
{
    /**
     * @var SnippetKeyGenerator
     */
    private $snippetKeyGenerator;

    /**
     * @var SnippetList
     */
    private $snippetList;

    public function __construct(SnippetKeyGenerator $snippetKeyGenerator, SnippetList $snippetList)
    {
        $this->snippetKeyGenerator = $snippetKeyGenerator;
        $this->snippetList = $snippetList;
    }

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
        $productId = ProductId::fromSku($productStockQuantitySource->getSku());
        $context = $productStockQuantitySource->getContext();

        $key = $this->snippetKeyGenerator->getKeyForContext($context, ['product_id' => $productId]);

        return $key;
    }
}
