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
     * @param ProjectionSourceData|ProductSource $product
     * @param EnvironmentSource $environmentSource
     * @throws InvalidArgumentException
     * @return SnippetResultList
     */
    public function render(ProjectionSourceData $product, EnvironmentSource $environmentSource)
    {
        if (!($product instanceof ProductSource)) {
            throw new InvalidArgumentException('First argument must be instance of Product.');
        }
        $this->renderProduct($product, $environmentSource);

        return $this->resultList;
    }

    /**
     * @param ProductSource $product
     * @param EnvironmentSource $environmentSource
     */
    private function renderProduct(ProductSource $product, EnvironmentSource $environmentSource)
    {
        foreach ($environmentSource->extractEnvironments($this->getEnvironmentParts()) as $environment) {
            $snippet = $this->renderProductInEnvironment($product, $environment);
            $this->resultList->add($snippet);
        }
    }

    /**
     * @param ProductSource $product
     * @param Environment $environment
     * @return SnippetResult
     */
    private function renderProductInEnvironment(ProductSource $product, Environment $environment)
    {
        $productInEnvironment = $product->getProductForEnvironment($environment);
        $layoutXmlPath = $this->getPathToLayoutXmlFile($environment);
        $snippetContent = $this->getSnippetContent($layoutXmlPath, $productInEnvironment);
        $snippetKey = $this->getKey($productInEnvironment->getId(), $environment);
        return SnippetResult::create($snippetKey, $snippetContent);
    }

    /**
     * @param ProductId $productId
     * @param Environment $environment
     * @return string
     */
    private function getKey(ProductId $productId, Environment $environment)
    {
        return $this->keyGenerator->getKeyForEnvironment($productId, $environment);
    }

    /**
     * @return array
     */
    private function getEnvironmentParts()
    {
        return [];
    }

    protected function getSnippetLayoutHandle()
    {
        return self::LAYOUT_HANDLE;
    }
}
