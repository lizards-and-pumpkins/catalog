<?php

namespace Brera\Product;

use Brera\Context\Context;
use Brera\Context\ContextSource;
use Brera\Renderer\BlockSnippetRenderer;
use Brera\SnippetResultList;
use Brera\ProjectionSourceData;
use Brera\SnippetResult;

class ProductDetailViewSnippetRenderer extends BlockSnippetRenderer
{
    const LAYOUT_HANDLE = 'product_details_snippet';

    /**
     * @param ProjectionSourceData|ProductSource $productSource
     * @param ContextSource $contextSource
     * @throws InvalidArgumentException
     * @return SnippetResultList
     */
    public function render(ProjectionSourceData $productSource, ContextSource $contextSource)
    {
        if (!($productSource instanceof ProductSource)) {
            throw new InvalidArgumentException('First argument must be instance of Product.');
        }
        $this->renderProduct($productSource, $contextSource);

        return $this->getSnippetResultList();
    }

    final protected function getSnippetLayoutHandle()
    {
        return self::LAYOUT_HANDLE;
    }

    /**
     * @param ProductSource $productSource
     * @param ContextSource $contextSource
     */
    private function renderProduct(ProductSource $productSource, ContextSource $contextSource)
    {
        foreach ($contextSource->extractContexts($this->getContextParts()) as $context) {
            $productInContext = $productSource->getProductForContext($context);
            $contentSnippet = $this->renderProductInContext($productInContext, $context);
            $urlSnippet = $this->getProductUrlKeySnippet($contentSnippet, $productInContext, $context);
            $childSnippetListSnippet = $this->getChildSnippetListSnippet($contentSnippet);
            $this->getSnippetResultList()->add($contentSnippet);
            $this->getSnippetResultList()->add($childSnippetListSnippet);
            $this->getSnippetResultList()->add($urlSnippet);
        }
    }

    /**
     * @param Product $product
     * @param Context $context
     * @return SnippetResult
     */
    private function renderProductInContext(Product $product, Context $context)
    {
        $layoutXmlPath = $this->getPathToLayoutXmlFile($context);
        $snippetContent = $this->getSnippetContent($layoutXmlPath, $product);
        $snippetKey = $this->getContentSnippetKey($product->getId(), $context);
        return SnippetResult::create($snippetKey, $snippetContent);
    }

    /**
     * @return string[]
     */
    private function getContextParts()
    {
        // todo: get context parts from outermost block
        return ['version', 'website', 'language'];
    }

    /**
     * @param SnippetResult $targetSnippet
     * @param Product $product
     * @param Context $context
     * @return SnippetResult
     */
    private function getProductUrlKeySnippet(SnippetResult $targetSnippet, Product $product, Context $context)
    {
        $snippetKey = $this->getUrlSnippetKey($product->getAttributeValue('url_key'), $context);
        $snippetContent = $targetSnippet->getKey();
        return SnippetResult::create($snippetKey, $snippetContent);
    }

    /**
     * @param SnippetResult $targetSnippet
     * @return SnippetResult
     */
    private function getChildSnippetListSnippet(SnippetResult $targetSnippet)
    {
        $snippetKey = $this->getUrlPathKeyGenerator()->getChildSnippetListKey($targetSnippet->getKey());
        $snippetContent = json_encode([$snippetKey]);
        return SnippetResult::create($snippetKey, $snippetContent);
    }

    /**
     * @param ProductId $productId
     * @param Context $context
     * @return string
     */
    private function getContentSnippetKey(ProductId $productId, Context $context)
    {
        return $this->getSnippetKeyGenerator()->getKeyForContext($productId, $context);
    }

    /**
     * @param string $urlKey
     * @param Context $context
     * @return string
     */
    private function getUrlSnippetKey($urlKey, Context $context)
    {
        return $this->getUrlPathKeyGenerator()->getUrlKeyForPathInContext($urlKey, $context);
    }
}
