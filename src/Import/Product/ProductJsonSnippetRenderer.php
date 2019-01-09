<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Product;

use LizardsAndPumpkins\Http\ContentDelivery\ProductJsonService\ProductJsonService;
use LizardsAndPumpkins\Import\Exception\InvalidDataObjectTypeException;
use LizardsAndPumpkins\Import\Product\View\ProductView;
use LizardsAndPumpkins\DataPool\KeyValueStore\Snippet;
use LizardsAndPumpkins\DataPool\KeyGenerator\SnippetKeyGenerator;
use LizardsAndPumpkins\Import\SnippetRenderer;
use LizardsAndPumpkins\ProductSearch\ContentDelivery\ProductSearchService;

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
     * @param ProductView $productView
     * @return Snippet[]
     */
    public function render($productView): array
    {
        if (! $productView instanceof ProductView) {
            throw new InvalidDataObjectTypeException(
                sprintf('Data object must be ProductView, got %s.', typeof($productView))
            );
        }

        return [
            $this->createProductJsonSnippet($productView)
        ];
    }

    private function createProductJsonSnippet(ProductView $product): Snippet
    {
        $key = $this->productJsonKeyGenerator->getKeyForContext(
            $product->getContext(),
            ['product_id' => $product->getId(), ProductJsonService::SNIPPET_NAME => '']
        );

        return Snippet::create($key, json_encode($product));
    }
}
