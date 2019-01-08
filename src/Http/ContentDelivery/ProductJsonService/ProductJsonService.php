<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Http\ContentDelivery\ProductJsonService;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\DataPool\DataPoolReader;
use LizardsAndPumpkins\DataPool\KeyGenerator\SnippetKeyGenerator;
use LizardsAndPumpkins\Http\ContentDelivery\ProductJsonService\Exception\SnippetNotFoundException;
use LizardsAndPumpkins\Import\Product\Product;
use LizardsAndPumpkins\Import\Product\ProductId;

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

    public function __construct(
        DataPoolReader $dataPoolReader,
        SnippetKeyGenerator $productJsonSnippetKeyGenerator,
        SnippetKeyGenerator $priceSnippetKeyGenerator,
        SnippetKeyGenerator $specialPriceSnippetKeyGenerator,
        EnrichProductJsonWithPrices $enrichProductJsonWithPrices
    ) {
        $this->dataPoolReader = $dataPoolReader;
        $this->productJsonSnippetKeyGenerator = $productJsonSnippetKeyGenerator;
        $this->priceSnippetKeyGenerator = $priceSnippetKeyGenerator;
        $this->specialPriceSnippetKeyGenerator = $specialPriceSnippetKeyGenerator;
        $this->enrichProductJsonWithPrices = $enrichProductJsonWithPrices;
    }

    /**
     * @param Context $context
     * @param ProductId[] $productIds
     * @return array[]
     */
    public function get(Context $context, string $snippetName, ProductId ...$productIds): array
    {
        return $this->buildProductData(
            $context,
            $this->getProductJsonSnippetKeys($context, $productIds, $snippetName),
            $this->getPriceSnippetKeys($context, $productIds),
            $this->getSpecialPriceSnippetKeys($context, $productIds)
        );
    }

    /**
     * @param Context $context
     * @param ProductId[] $productIds
     * @return string[]
     */
    private function getProductJsonSnippetKeys(Context $context, array $productIds, string $snippetName): array
    {
        return $this->getSnippetKeysForJson($context, $productIds, $snippetName, $this->productJsonSnippetKeyGenerator);
    }

    /**
     * @param Context $context
     * @param ProductId[] $productIds
     * @param SnippetKeyGenerator $keyGenerator
     * @return string[]
     */
    private function getSnippetKeysForJson(
        Context $context,
        array $productIds,
        string $snippetName,
        SnippetKeyGenerator $keyGenerator
    ): array {
        return array_map(function (ProductId $productId) use ($context, $keyGenerator, $snippetName) {
            return $keyGenerator->getKeyForContext($context, [Product::ID => $productId, 'snippetName' => $snippetName]);
        }, $productIds);
    }

    /**
     * @param Context $context
     * @param ProductId[] $productIds
     * @return string[]
     */
    private function getPriceSnippetKeys(Context $context, array $productIds): array
    {
        return $this->getSnippetKeys($context, $productIds, $this->priceSnippetKeyGenerator);
    }

    /**
     * @param Context $context
     * @param ProductId[] $productIds
     * @return string[]
     */
    private function getSpecialPriceSnippetKeys(Context $context, array $productIds): array
    {
        return $this->getSnippetKeys($context, $productIds, $this->specialPriceSnippetKeyGenerator);
    }

    /**
     * @param Context $context
     * @param ProductId[] $productIds
     * @param SnippetKeyGenerator $keyGenerator
     * @return string[]
     */
    private function getSnippetKeys(Context $context, array $productIds, SnippetKeyGenerator $keyGenerator): array
    {
        return array_map(function (ProductId $productId) use ($context, $keyGenerator) {
            return $keyGenerator->getKeyForContext($context, [Product::ID => $productId]);
        }, $productIds);
    }

    /**
     * @param Context $context
     * @param string[] $productJsonSnippetKeys
     * @param string[] $priceSnippetKeys
     * @param string[] $specialPriceSnippetKeys
     * @return array[]
     */
    private function buildProductData(
        Context $context,
        array $productJsonSnippetKeys,
        array $priceSnippetKeys,
        array $specialPriceSnippetKeys
    ): array {
        $snippets = $this->getSnippets($productJsonSnippetKeys, $priceSnippetKeys, $specialPriceSnippetKeys);

        return array_map(function ($productJsonSnippetKey, $priceKey, $specialPriceKey) use ($context, $snippets) {
            if (null === $snippets[$productJsonSnippetKey]) {
                throw new SnippetNotFoundException(
                    sprintf('Snippet with key %s not found.', $productJsonSnippetKey)
                );
            }
            return $this->enrichProductJsonWithPrices->addPricesToProductData(
                $context,
                json_decode($snippets[$productJsonSnippetKey], true),
                $snippets[$priceKey],
                $snippets[$specialPriceKey] ?? null
            );
        }, $productJsonSnippetKeys, $priceSnippetKeys, $specialPriceSnippetKeys);
    }

    /**
     * @param string[] $productJsonSnippetKeys
     * @param string[] $priceSnippetKeys
     * @param string[] $specialPriceSnippetKeys
     * @return string[]
     */
    private function getSnippets(
        array $productJsonSnippetKeys,
        array $priceSnippetKeys,
        array $specialPriceSnippetKeys
    ): array {
        $keys = array_merge($productJsonSnippetKeys, $priceSnippetKeys, $specialPriceSnippetKeys);
        return $this->dataPoolReader->getSnippets($keys);
    }
}
