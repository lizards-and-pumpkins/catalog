<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Http\ContentDelivery\PageBuilder\SnippetTransformation;

use LizardsAndPumpkins\Http\ContentDelivery\ProductJsonService\EnrichProductJsonWithPrices;
use LizardsAndPumpkins\Http\ContentDelivery\PageBuilder\PageSnippets;
use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Import\Price\PriceSnippetRenderer;
use LizardsAndPumpkins\Import\SnippetCode;

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
     * @param mixed $input
     * @param Context $context
     * @param PageSnippets $pageSnippets
     * @return string
     */
    public function __invoke($input, Context $context, PageSnippets $pageSnippets) : string
    {
        $price = $pageSnippets->getSnippetByCode(new SnippetCode(PriceSnippetRenderer::PRICE));
        $specialPrice = $this->getSpecialPrice($pageSnippets);
        $productData = json_decode($input, true);
        $enrichedProductData = $this->enrichProductJson->addPricesToProductData(
            $context,
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
        $snippetCode = new SnippetCode(PriceSnippetRenderer::SPECIAL_PRICE);

        return $pageSnippets->hasSnippetCode($snippetCode) ?
            $pageSnippets->getSnippetByCode($snippetCode) :
            null;
    }
}
