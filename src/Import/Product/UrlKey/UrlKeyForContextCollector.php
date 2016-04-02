<?php

namespace LizardsAndPumpkins\Import\Product\UrlKey;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\ContextSource;
use LizardsAndPumpkins\Import\Product\Product;
use LizardsAndPumpkins\ProductListing\Import\ProductListing;


class UrlKeyForContextCollector
{
    const URL_KEY_TYPE_LISTING = 'listing';
    const URL_KEY_TYPE_PRODUCT = 'product';
    
    /**
     * @var ContextSource
     */
    private $contextSource;

    public function __construct(ContextSource $contextSource)
    {
        $this->contextSource = $contextSource;
    }
    
    /**
     * @param Product $product
     * @return UrlKeyForContextCollection
     */
    public function collectProductUrlKeys(Product $product)
    {
        $urlKey = UrlKey::fromString($product->getFirstValueOfAttribute(Product::URL_KEY));
        $urlKeyForContext = new UrlKeyForContext($urlKey, $product->getContext(), self::URL_KEY_TYPE_PRODUCT);
        return new UrlKeyForContextCollection($urlKeyForContext);
    }

    /**
     * @param ProductListing $listingCriteria
     * @return UrlKeyForContextCollection
     */
    public function collectListingUrlKeys(ProductListing $listingCriteria)
    {
        $contexts = $this->contextSource->getContextsForParts(array_keys($listingCriteria->getContextData()));
        $urlKeysForContexts = $this->getListingUrlKeysForContexts($listingCriteria, $contexts);
        return new UrlKeyForContextCollection(...$urlKeysForContexts);
    }

    /**
     * @param ProductListing $listingCriteria
     * @param Context[] $contexts
     * @return UrlKeyForContext[]
     */
    private function getListingUrlKeysForContexts(ProductListing $listingCriteria, array $contexts)
    {
        return array_map(function (Context $context) use ($listingCriteria) {
            return new UrlKeyForContext($listingCriteria->getUrlKey(), $context, self::URL_KEY_TYPE_LISTING);
        }, $contexts);
    }
}
