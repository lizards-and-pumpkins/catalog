<?php

namespace LizardsAndPumpkins\ProductDetail;

use LizardsAndPumpkins\Context\BaseUrl\BaseUrlBuilder;
use LizardsAndPumpkins\Import\Product\Product;
use LizardsAndPumpkins\Import\Product\View\ProductView;
use LizardsAndPumpkins\DataPool\KeyValueStore\Snippet;
use LizardsAndPumpkins\DataPool\KeyGenerator\SnippetKeyGenerator;
use LizardsAndPumpkins\Import\SnippetRenderer;

class ProductCanonicalTagSnippetRenderer implements SnippetRenderer
{
    const CODE = 'product_canonical_tag';
    
    /**
     * @var SnippetKeyGenerator
     */
    private $canonicalTagSnippetKeyGenerator;

    /**
     * @var BaseUrlBuilder
     */
    private $baseUrlBuilder;

    public function __construct(
        SnippetKeyGenerator $canonicalTagSnippetKeyGenerator,
        BaseUrlBuilder $baseUrlBuilder
    ) {
        $this->canonicalTagSnippetKeyGenerator = $canonicalTagSnippetKeyGenerator;
        $this->baseUrlBuilder = $baseUrlBuilder;
    }
    
    /**
     * @param ProductView $productView
     * @return Snippet[]
     */
    public function render(ProductView $productView)
    {
        return [Snippet::create($this->createSnippetKey($productView), $this->createSnippetContent($productView))];
    }

    /**
     * @param ProductView $productView
     * @return string
     */
    private function createSnippetContent(ProductView $productView)
    {
        $urlKey = $productView->getFirstValueOfAttribute(Product::URL_KEY);
        $baseUrl = $this->baseUrlBuilder->create($productView->getContext());
        return sprintf('<link rel="canonical" href="%s%s" />', $baseUrl, $urlKey);
    }

    /**
     * @param ProductView $productView
     * @return string
     */
    private function createSnippetKey(ProductView $productView)
    {
        return $this->canonicalTagSnippetKeyGenerator->getKeyForContext(
            $productView->getContext(),
            [Product::ID => $productView->getId()]
        );
    }
}
