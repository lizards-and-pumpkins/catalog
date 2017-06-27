<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductListing\Import;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\ContextBuilder;
use LizardsAndPumpkins\Import\Exception\InvalidDataObjectTypeException;
use LizardsAndPumpkins\Import\PageMetaInfoSnippetContent;
use LizardsAndPumpkins\DataPool\KeyGenerator\SnippetKeyGenerator;
use LizardsAndPumpkins\Import\SnippetRenderer;
use LizardsAndPumpkins\DataPool\KeyValueStore\Snippet;
use LizardsAndPumpkins\ProductListing\Import\TemplateRendering\ProductListingBlockRenderer;

class ProductListingSnippetRenderer implements SnippetRenderer
{
    const CODE = 'product_listing_meta';

    /**
     * @var ProductListingBlockRenderer
     */
    private $blockRenderer;

    /**
     * @var SnippetKeyGenerator
     */
    private $metaSnippetKeyGenerator;

    /**
     * @var ContextBuilder
     */
    private $contextBuilder;

    public function __construct(
        ProductListingBlockRenderer $blockRenderer,
        SnippetKeyGenerator $metaSnippetKeyGenerator,
        ContextBuilder $contextBuilder
    ) {
        $this->blockRenderer = $blockRenderer;
        $this->metaSnippetKeyGenerator = $metaSnippetKeyGenerator;
        $this->contextBuilder = $contextBuilder;
    }

    /**
     * @param ProductListing $productListing
     * @return Snippet[]
     */
    public function render($productListing): array
    {
        if (! $productListing instanceof ProductListing) {
            throw new InvalidDataObjectTypeException(
                sprintf('Data object must be ProductListing, got %s.', typeof($productListing))
            );
        }

        return [
            $this->createPageMetaSnippet($productListing),
        ];
    }

    private function createPageMetaSnippet(ProductListing $productListing): Snippet
    {
        $metaDataSnippetKey = $this->getProductListingMetaDataSnippetKey($productListing);
        $metaDataSnippetContent = json_encode($this->getPageMetaInfoSnippetContent($productListing));
        return Snippet::create($metaDataSnippetKey, $metaDataSnippetContent);
    }

    private function getProductListingMetaDataSnippetKey(ProductListing $productListing): string
    {
        $productListingUrlKey = $productListing->getUrlKey();
        $snippetKey = $this->metaSnippetKeyGenerator->getKeyForContext(
            $this->getContextFromProductListingData($productListing),
            [PageMetaInfoSnippetContent::URL_KEY => $productListingUrlKey]
        );

        return $snippetKey;
    }

    private function getPageMetaInfoSnippetContent(ProductListing $productListing): ProductListingSnippetContent
    {
        return ProductListingSnippetContent::create(
            $productListing->getCriteria(),
            ProductListingTemplateSnippetRenderer::CODE,
            $this->getPageSnippetCodes($productListing),
            []
        );
    }

    /**
     * @param ProductListing $productListing
     * @return string[]
     */
    private function getPageSnippetCodes(ProductListing $productListing): array
    {
        $context = $this->getContextFromProductListingData($productListing);
        $this->blockRenderer->render($productListing, $context);
        return $this->blockRenderer->getNestedSnippetCodes();
    }

    private function getContextFromProductListingData(ProductListing $productListing): Context
    {
        $contextData = $productListing->getContextData();
        return $this->contextBuilder->createContext($contextData);
    }
}
