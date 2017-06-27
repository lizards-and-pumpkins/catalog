<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductDetail;

use LizardsAndPumpkins\Import\Exception\InvalidDataObjectTypeException;
use LizardsAndPumpkins\Import\PageMetaInfoSnippetContent;
use LizardsAndPumpkins\Import\Product\Product;
use LizardsAndPumpkins\Import\Product\View\ProductView;
use LizardsAndPumpkins\DataPool\KeyValueStore\Snippet;
use LizardsAndPumpkins\DataPool\KeyGenerator\SnippetKeyGenerator;
use LizardsAndPumpkins\Import\SnippetRenderer;
use LizardsAndPumpkins\ProductDetail\TemplateRendering\ProductDetailViewBlockRenderer;

class ProductDetailMetaSnippetRenderer implements SnippetRenderer
{
    const CODE = 'product_detail_meta';

    /**
     * @var ProductDetailViewBlockRenderer
     */
    private $blockRenderer;

    /**
     * @var SnippetKeyGenerator
     */
    private $snippetKeyGenerator;

    public function __construct(ProductDetailViewBlockRenderer $blockRenderer, SnippetKeyGenerator $snippetKeyGenerator)
    {
        $this->blockRenderer = $blockRenderer;
        $this->snippetKeyGenerator = $snippetKeyGenerator;
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

        return $this->createProductDetailPageMetaSnippets($productView);
    }

    /**
     * @param ProductView $productView
     * @return Snippet[]
     */
    private function createProductDetailPageMetaSnippets(ProductView $productView): array
    {
        $pageMetaData = json_encode($this->getPageMetaSnippetContent($productView));
        return array_map(function ($urlKey) use ($pageMetaData, $productView) {
            $key = $this->createPageMetaSnippetKey($urlKey, $productView);
            return Snippet::create($key, $pageMetaData);
        }, $this->getAllProductUrlKeys($productView));
    }

    private function getPageMetaSnippetContent(ProductView $productView): ProductDetailPageMetaInfoSnippetContent
    {
        $this->blockRenderer->render($productView, $productView->getContext());

        return ProductDetailPageMetaInfoSnippetContent::create(
            (string) $productView->getId(),
            $this->blockRenderer->getRootSnippetCode(),
            $this->blockRenderer->getNestedSnippetCodes(),
            []
        );
    }

    private function createPageMetaSnippetKey(string $urlKey, ProductView $productView): string
    {
        return $this->snippetKeyGenerator->getKeyForContext(
            $productView->getContext(),
            [PageMetaInfoSnippetContent::URL_KEY => $urlKey]
        );
    }

    /**
     * @param ProductView $productView
     * @return string[]
     */
    private function getAllProductUrlKeys(ProductView $productView): array
    {
        return array_merge(
            [$productView->getFirstValueOfAttribute(Product::URL_KEY)],
            $productView->getAllValuesOfAttribute(Product::NON_CANONICAL_URL_KEY)
        );
    }
}
