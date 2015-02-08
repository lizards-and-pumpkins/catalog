<?php

namespace Brera\Product;

use Brera\Environment\Environment;
use Brera\Environment\EnvironmentSource;
use Brera\Renderer\BlockSnippetRenderer;
use Brera\EnvironmentSource;
use Brera\SnippetResultList;
use Brera\ProjectionSourceData;
use Brera\SnippetResult;

class ProductDetailViewSnippetRenderer extends BlockSnippetRenderer
{
    const LAYOUT_HANDLE = 'product_details_snippet';

    /**
     * @var SnippetResultList
     */
    private $resultList;

    /**
     * @var HardcodedProductDetailViewSnippetKeyGenerator
     */
    private $keyGenerator;

    /**
     * @param SnippetResultList $resultList
     * @param HardcodedProductDetailViewSnippetKeyGenerator $keyGenerator
     */
    public function __construct(
        SnippetResultList $resultList,
        HardcodedProductDetailViewSnippetKeyGenerator $keyGenerator
    ) {
        $this->resultList = $resultList;
        $this->keyGenerator = $keyGenerator;
    }

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
        $snippetContent = $this->getSnippetContent($this->getPathToLayoutXmlFile(), $product);
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
