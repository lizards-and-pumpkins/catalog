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

        return $this->resultList;
    }

    /**
     * @param ProductSource $productSource
     * @param EnvironmentSource $environmentSource
     */
    private function renderProduct(ProductSource $productSource, EnvironmentSource $environmentSource)
    {
        foreach ($environmentSource->extractEnvironments($this->getEnvironmentParts()) as $environment) {
            $productInEnvironment = $productSource->getProductForEnvironment($environment);
            $snippet = $this->renderProductInEnvironment($productInEnvironment, $environment);
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
        $snippetKey = $this->getKey($product, $environment);
        return SnippetResult::create($snippetKey, $snippetContent);
    }

    /**
     * @param Product $product
     * @param Environment $environment
     * @return string
     */
    private function getKey(Product $product, Environment $environment)
    {
        return $this->keyGenerator->getKeyForEnvironment($product, $environment);
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
