<?php

namespace LizardsAndPumpkins\ContentDelivery\Catalog;

use LizardsAndPumpkins\ContentDelivery\SnippetTransformation\Exception\NoValidLocaleInContextException;
use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\ContextBuilder\ContextLocale;
use LizardsAndPumpkins\DataPool\DataPoolReader;
use LizardsAndPumpkins\Product\Product;
use LizardsAndPumpkins\Product\ProductId;
use LizardsAndPumpkins\SnippetKeyGenerator;
use SebastianBergmann\Money\EUR;
use SebastianBergmann\Money\IntlFormatter;

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
     * @var Context
     */
    private $context;

    public function __construct(
        DataPoolReader $dataPoolReader,
        SnippetKeyGenerator $productJsonSnippetKeyGenerator,
        SnippetKeyGenerator $priceSnippetKeyGenerator,
        SnippetKeyGenerator $specialPriceSnippetKeyGenerator,
        Context $context
    ) {
        $this->dataPoolReader = $dataPoolReader;
        $this->productJsonSnippetKeyGenerator = $productJsonSnippetKeyGenerator;
        $this->priceSnippetKeyGenerator = $priceSnippetKeyGenerator;
        $this->specialPriceSnippetKeyGenerator = $specialPriceSnippetKeyGenerator;
        $this->context = $context;
    }

    /**
     * @param ProductId[] $productIds
     * @return array[]
     */
    public function get(ProductId ...$productIds)
    {
        $productsData = $this->buildProductData(
            $this->getProductJsonSnippetKeys($productIds),
            $this->getPriceSnippetKeys($productIds),
            $this->getSpecialPriceSnippetKeys($productIds)
        );
        
        return $productsData;
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
            $productData = json_decode($snippets[$productJsonSnippetKey], true);
            return $this->addPricesToProductData($productData, $snippets[$priceKey], $snippets[$specialPriceKey]);
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

    /**
     * @param string[] $productData
     * @param string $price
     * @param string $specialPrice
     * @return array[]
     */
    public function addPricesToProductData(array $productData, $price, $specialPrice)
    {
        $productData['attributes']['raw_price'] = $price;
        $productData['attributes']['price'] = $this->formatPriceSnippet($price);

        $productData['attributes']['raw_special_price'] = $specialPrice;
        $productData['attributes']['special_price'] = $this->formatPriceSnippet($specialPrice);
        
        return $productData;
    }

    /**
     * @param string $price
     * @return string
     */
    public function formatPriceSnippet($price)
    {
        $locale = $this->getLocaleString($this->context);
        return (new IntlFormatter($locale))->format(new EUR((int) $price));
    }

    /**
     * @param Context $context
     * @return string
     */
    private function getLocaleString(Context $context)
    {
        $locale = $context->getValue(ContextLocale::CODE);
        if (is_null($locale)) {
            throw new NoValidLocaleInContextException('No valid locale in context');
        }
        return $locale;
    }
}
