<?php

namespace Brera\Product;

use Brera\Renderer\BlockSnippetRenderer;
use Brera\EnvironmentSource;
use Brera\SnippetResultList;
use Brera\ProjectionSourceData;
use Brera\Environment;
use Brera\SnippetResult;

class ProductDetailViewSnippetRenderer extends BlockSnippetRenderer
{
    const LAYOUT_HANDLE = 'product_details_snippet';

    /**
     * @param ProjectionSourceData|Product $product
     * @param EnvironmentSource $environmentSource
     * @throws InvalidArgumentException
     * @return SnippetResultList
     */
    public function render(ProjectionSourceData $product, EnvironmentSource $environmentSource)
    {
        if (!($product instanceof Product)) {
            throw new InvalidArgumentException('First argument must be instance of Product.');
        }
        $this->renderProduct($product, $environmentSource);

        return $this->resultList;
    }

    /**
     * @param Product $product
     * @param EnvironmentSource $environmentSource
     */
    private function renderProduct(Product $product, EnvironmentSource $environmentSource)
    {
        foreach ($environmentSource->extractEnvironments($this->getEnvironmentParts()) as $environment) {
            $snippet = $this->renderProductInEnvironment($product, $environment);
            $this->resultList->add($snippet);
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
        $snippetKey = $this->getKey($product->getId(), $environment);

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
