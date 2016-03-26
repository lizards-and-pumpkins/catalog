<?php

namespace LizardsAndPumpkins\Http\ContentDelivery\ProductJsonService;

use LizardsAndPumpkins\Http\ContentDelivery\ProductJsonService\EnrichProductJsonWithPrices;
use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\DataPool\DataPoolReader;
use LizardsAndPumpkins\Import\Product\Product;
use LizardsAndPumpkins\Import\Product\ProductId;
use LizardsAndPumpkins\DataPool\KeyGenerator\SnippetKeyGenerator;

class ProductJsonService
{
    /**
     * @var DataPoolReader
     */
    private $dataPoolReader;

    /**
     * @var SnippetKeyGenerator
     */
    private $productJsonSnippetKeyGenerator;

    /**
     * @var SnippetKeyGenerator
     */
    private $priceSnippetKeyGenerator;

    /**
     * @var SnippetKeyGenerator
     */
    private $specialPriceSnippetKeyGenerator;

    /**
     * @var EnrichProductJsonWithPrices
     */
    private $enrichProductJsonWithPrices;

    /**
     * @var Context
     */
    private $context;

    public function __construct(
        DataPoolReader $dataPoolReader,
        SnippetKeyGenerator $productJsonSnippetKeyGenerator,
        SnippetKeyGenerator $priceSnippetKeyGenerator,
        SnippetKeyGenerator $specialPriceSnippetKeyGenerator,
        EnrichProductJsonWithPrices $enrichProductJsonWithPrices,
        Context $context
    ) {
        $this->dataPoolReader = $dataPoolReader;
        $this->productJsonSnippetKeyGenerator = $productJsonSnippetKeyGenerator;
        $this->priceSnippetKeyGenerator = $priceSnippetKeyGenerator;
        $this->specialPriceSnippetKeyGenerator = $specialPriceSnippetKeyGenerator;
        $this->enrichProductJsonWithPrices = $enrichProductJsonWithPrices;
        $this->context = $context;
    }

    /**
     * @param ProductId[] $productIds
     * @return array[]
     */
    public function get(ProductId ...$productIds)
    {
        return $this->buildProductData(
            $this->getProductJsonSnippetKeys($productIds),
            $this->getPriceSnippetKeys($productIds),
            $this->getSpecialPriceSnippetKeys($productIds)
        );
    }

    /**
     * @param ProductId[] $productIds
     * @return string[]
     */
    private function getProductJsonSnippetKeys(array $productIds)
    {
        return $this->getSnippetKeys($productIds, $this->productJsonSnippetKeyGenerator);
    }

    /**
     * @param ProductId[] $productIds
     * @return string[]
     */
    private function getPriceSnippetKeys(array $productIds)
    {
        return $this->getSnippetKeys($productIds, $this->priceSnippetKeyGenerator);
    }

    /**
     * @param ProductId[] $productIds
     * @return string[]
     */
    private function getSpecialPriceSnippetKeys(array $productIds)
    {
        return $this->getSnippetKeys($productIds, $this->specialPriceSnippetKeyGenerator);
    }

    /**
     * @param ProductId[] $productIds
     * @param SnippetKeyGenerator $keyGenerator
     * @return string[]
     */
    private function getSnippetKeys(array $productIds, SnippetKeyGenerator $keyGenerator)
    {
        return array_map(function (ProductId $productId) use ($keyGenerator) {
            return $keyGenerator->getKeyForContext($this->context, [Product::ID => $productId]);
        }, $productIds);
    }

    /**
     * @param string[] $productJsonSnippetKeys
     * @param string[] $priceSnippetKeys
     * @param string[] $specialPriceSnippetKeys
     * @return array[]
     */
    private function buildProductData($productJsonSnippetKeys, $priceSnippetKeys, $specialPriceSnippetKeys)
    {
        $snippets = $this->getSnippets($productJsonSnippetKeys, $priceSnippetKeys, $specialPriceSnippetKeys);

        return array_map(function ($productJsonSnippetKey, $priceKey, $specialPriceKey) use ($snippets) {
            return $this->enrichProductJsonWithPrices->addPricesToProductData(
                json_decode($snippets[$productJsonSnippetKey], true),
                $snippets[$priceKey],
                @$snippets[$specialPriceKey]
            );
        }, $productJsonSnippetKeys, $priceSnippetKeys, $specialPriceSnippetKeys);
    }

    /**
     * @param string[] $productJsonSnippetKeys
     * @param string[] $priceSnippetKeys
     * @param string[] $specialPriceSnippetKeys
     * @return string[]
     */
    private function getSnippets($productJsonSnippetKeys, $priceSnippetKeys, $specialPriceSnippetKeys)
    {
        $keys = array_merge($productJsonSnippetKeys, $priceSnippetKeys, $specialPriceSnippetKeys);
        return $this->dataPoolReader->getSnippets($keys);
    }
}
