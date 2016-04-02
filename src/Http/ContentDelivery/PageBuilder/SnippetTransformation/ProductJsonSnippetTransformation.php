<?php

namespace LizardsAndPumpkins\Http\ContentDelivery\PageBuilder\SnippetTransformation;

use LizardsAndPumpkins\Http\ContentDelivery\ProductJsonService\EnrichProductJsonWithPrices;
use LizardsAndPumpkins\Http\ContentDelivery\PageBuilder\PageSnippets;
use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Import\Price\PriceSnippetRenderer;

class ProductJsonSnippetTransformation implements SnippetTransformation
{
    /**
     * @var EnrichProductJsonWithPrices
     */
    private $enrichProductJson;

    public function __construct(EnrichProductJsonWithPrices $enrichProductJsonWithPrices)
    {
        $this->enrichProductJson = $enrichProductJsonWithPrices;
    }

    /**
     * @param string $input
     * @param Context $context
     * @param PageSnippets $pageSnippets
     * @return string
     */
    public function __invoke($input, Context $context, PageSnippets $pageSnippets)
    {
        $price = $pageSnippets->getSnippetByCode(PriceSnippetRenderer::PRICE);
        $specialPrice = $this->getSpecialPrice($pageSnippets);
        $productData = json_decode($input, true);
        $enrichedProductData = $this->enrichProductJson->addPricesToProductData($productData, $price, $specialPrice);
        return json_encode($enrichedProductData);
    }

    /**
     * @param PageSnippets $pageSnippets
     * @return string
     */
    private function getSpecialPrice(PageSnippets $pageSnippets)
    {
        return $pageSnippets->hasSnippetCode(PriceSnippetRenderer::SPECIAL_PRICE) ?
            $pageSnippets->getSnippetByCode(PriceSnippetRenderer::SPECIAL_PRICE) :
            null;
    }
}
