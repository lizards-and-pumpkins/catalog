<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Http\ContentDelivery\PageBuilder\SnippetTransformation;

use LizardsAndPumpkins\Http\ContentDelivery\PageBuilder\PageSnippets;
use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Http\ContentDelivery\ProductJsonService\EnrichProductJsonWithPricesBuilder;
use LizardsAndPumpkins\Import\Price\PriceSnippetRenderer;

class ProductJsonSnippetTransformation implements SnippetTransformation
{
    /**
     * @var EnrichProductJsonWithPricesBuilder
     */
    private $enrichProductJsonWithPricesBuilder;

    public function __construct(EnrichProductJsonWithPricesBuilder $enrichProductJsonWithPricesBuilder)
    {
        $this->enrichProductJsonWithPricesBuilder = $enrichProductJsonWithPricesBuilder;
    }

    /**
     * @param mixed $input
     * @param Context $context
     * @param PageSnippets $pageSnippets
     * @return string
     */
    public function __invoke($input, Context $context, PageSnippets $pageSnippets) : string
    {
        $price = $pageSnippets->getSnippetByCode(PriceSnippetRenderer::PRICE);
        $specialPrice = $this->getSpecialPrice($pageSnippets);
        $productData = json_decode($input, true);
        $enrichProductJsonWithPrices = $this->enrichProductJsonWithPricesBuilder->getForContext($context);
        $enrichedProductData = $enrichProductJsonWithPrices->addPricesToProductData(
            $productData,
            $price,
            $specialPrice
        );

        return json_encode($enrichedProductData);
    }

    /**
     * @param PageSnippets $pageSnippets
     * @return string|null
     */
    private function getSpecialPrice(PageSnippets $pageSnippets)
    {
        return $pageSnippets->hasSnippetCode(PriceSnippetRenderer::SPECIAL_PRICE) ?
            $pageSnippets->getSnippetByCode(PriceSnippetRenderer::SPECIAL_PRICE) :
            null;
    }
}
