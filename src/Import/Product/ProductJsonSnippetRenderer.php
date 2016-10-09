<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Product;

use LizardsAndPumpkins\Import\Product\View\ProductView;
use LizardsAndPumpkins\DataPool\KeyValueStore\Snippet;
use LizardsAndPumpkins\DataPool\KeyGenerator\SnippetKeyGenerator;
use LizardsAndPumpkins\Import\SnippetRenderer;

class ProductJsonSnippetRenderer implements SnippetRenderer
{
    const CODE = 'product_json';

    /**
     * @var SnippetKeyGenerator
     */
    private $productJsonKeyGenerator;

    public function __construct(SnippetKeyGenerator $productJsonKeyGenerator)
    {
        $this->productJsonKeyGenerator = $productJsonKeyGenerator;
    }

    /**
     * @param ProductView $product
     * @return Snippet[]
     */
    public function render(ProductView $product) : array
    {
        return [
            $this->createProductJsonSnippet($product)
        ];
    }

    private function createProductJsonSnippet(ProductView $product) : Snippet
    {
        $key = $this->productJsonKeyGenerator->getKeyForContext(
            $product->getContext(),
            ['product_id' => $product->getId()]
        );

        return Snippet::create($key, json_encode($product));
    }
}
