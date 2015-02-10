<?php

namespace Brera\Product;

use Brera\Environment\Environment;
use Brera\Environment\EnvironmentSource;
use Brera\Renderer\BlockSnippetRenderer;
use Brera\SnippetResultList;
use Brera\ProjectionSourceData;
use Brera\SnippetResult;

class ProductDetailViewSnippetRenderer extends BlockSnippetRenderer
{
    const LAYOUT_HANDLE = 'product_details_snippet';

    /**
     * @param ProjectionSourceData|ProductSource $productSource
     * @param EnvironmentSource $environmentSource
     * @throws InvalidArgumentException
     * @return SnippetResultList
     */
    public function render(ProjectionSourceData $productSource, EnvironmentSource $environmentSource)
    {
        if (!($productSource instanceof ProductSource)) {
            throw new InvalidArgumentException('First argument must be instance of Product.');
        }
        $this->renderProduct($productSource, $environmentSource);

        return $this->getSnippetResultList();
    }

    final protected function getSnippetLayoutHandle()
    {
        return self::LAYOUT_HANDLE;
    }

    /**
     * @param ProductSource $productSource
     * @param EnvironmentSource $environmentSource
     */
    private function renderProduct(ProductSource $productSource, EnvironmentSource $environmentSource)
    {
        foreach ($environmentSource->extractEnvironments($this->getEnvironmentParts()) as $environment) {
            $productInEnvironment = $productSource->getProductForEnvironment($environment);
            $contentSnippet = $this->renderProductInEnvironment($productInEnvironment, $environment);
            $urlSnippet = $this->getProductUrlKeySnippet($contentSnippet, $productInEnvironment, $environment);
            $this->getSnippetResultList()->add($contentSnippet);
            $this->getSnippetResultList()->add($urlSnippet);
        }
    }

    /**
     * @param Product $product
     * @param Environment $environment
     * @return SnippetResult
     */
    private function renderProductInEnvironment(Product $product, Environment $environment)
    {
        $layoutXmlPath = $this->getPathToLayoutXmlFile($environment);
        $snippetContent = $this->getSnippetContent($layoutXmlPath, $product);
        $snippetKey = $this->getContentSnippetKey($product->getId(), $environment);
        return SnippetResult::create($snippetKey, $snippetContent);
    }

    /**
     * @return array
     */
    private function getEnvironmentParts()
    {
        // todo: get environment parts from outermost block
        return ['version', 'website', 'language'];
    }

    /**
     * @param SnippetResult $targetSnippet
     * @param Product $product
     * @param Environment $environment
     * @return SnippetResult
     */
    private function getProductUrlKeySnippet(SnippetResult $targetSnippet, Product $product, Environment $environment)
    {
        $snippetKey = $this->getUrlSnippetKey($product->getAttributeValue('url_key'), $environment);
        $snippetContent = $targetSnippet->getKey();
        return SnippetResult::create($snippetKey, $snippetContent);
    }

    /**
     * @param ProductId $productId
     * @param Environment $environment
     * @return string
     */
    private function getContentSnippetKey(ProductId $productId, Environment $environment)
    {
        return $this->getSnippetKeyGenerator()->getKeyForEnvironment($productId, $environment);
    }

    /**
     * @param string $urlKey
     * @param Environment $environment
     * @return string
     */
    private function getUrlSnippetKey($urlKey, Environment $environment)
    {
        return $this->getUrlPathKeyGenerator()->getUrlKeyForPathInEnvironment($urlKey, $environment);
    }
}
