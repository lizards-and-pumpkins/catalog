<?php

declare(strict_types=1);

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
    
    public function collectProductUrlKeys(Product $product) : UrlKeyForContextCollection
    {
        $urlKey = UrlKey::fromString($product->getFirstValueOfAttribute(Product::URL_KEY));
        $urlKeyForContext = new UrlKeyForContext($urlKey, $product->getContext(), self::URL_KEY_TYPE_PRODUCT);
        return new UrlKeyForContextCollection($urlKeyForContext);
    }

    public function collectListingUrlKeys(ProductListing $listingCriteria) : UrlKeyForContextCollection
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
    private function getListingUrlKeysForContexts(ProductListing $listingCriteria, array $contexts) : array
    {
        return array_map(function (Context $context) use ($listingCriteria) {
            return new UrlKeyForContext($listingCriteria->getUrlKey(), $context, self::URL_KEY_TYPE_LISTING);
        }, $contexts);
    }
}
