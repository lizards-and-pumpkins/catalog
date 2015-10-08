<?php


namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Snippet;
use LizardsAndPumpkins\SnippetKeyGenerator;
use LizardsAndPumpkins\SnippetList;

class ProductJsonSnippetRenderer
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
     * @param Product $product
     * @return SnippetList
     */
    public function render(Product $product)
    {
        $snippetList = new SnippetList();
        $key = $this->productJsonKeyGenerator->getKeyForContext(
            $product->getContext(),
            ['product_id' => $product->getId()]
        );
        $content = json_encode($product);
        $snippetList->add(Snippet::create($key, $content));
        return $snippetList;
    }
}
