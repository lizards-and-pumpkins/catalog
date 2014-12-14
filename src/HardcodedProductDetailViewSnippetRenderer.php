<?php

namespace Brera\PoC;


use Brera\PoC\Product\Product;
use Psr\Log\InvalidArgumentException;

class HardcodedProductDetailViewSnippetRenderer implements SnippetRenderer
{
    /**
     * @var SnippetResultList
     */
    private $resultList;
    /**
     * @var HardcodedProductDetailViewSnippetKeyGenerator
     */
    private $keyGenerator;

    /**
     * @param SnippetResultList                             $resultList
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
     * @param Environment $environment
     *
     * @return SnippetResultList
     */
    public function render(ProjectionSourceData $product, Environment $environment)
    {
        if (!($product instanceof Product)) {
            throw new InvalidArgumentException('First argument must be instance of Product.');
        }

        return $this->renderProduct($product, $environment);
    }

    /**
     * @param Product $product
     * @param Environment $environment
     * @return SnippetResultList
     */
    private function renderProduct(Product $product, Environment $environment)
    {
        $snippet = SnippetResult::create(
            $this->getKey($product, $environment),
            $this->getContent($product, $environment)
        );
        $this->resultList->add($snippet);

        return $this->resultList;
    }

    /**
     * @param Product $product
     * @param Environment $environment
     * @return string
     */
    private function getContent(Product $product, Environment $environment)
    {
        return '<div>' . htmlentities($product->getName()) . '</div>';
    }

    /**
     * @param Product $product
     * @param Environment $environment
     * @return string
     */
    private function getKey(Product $product, Environment $environment)
    {
        return $this->keyGenerator->getKey($product, $environment);
    }
}
