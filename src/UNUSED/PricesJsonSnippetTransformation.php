<?php

namespace LizardsAndPumpkins\UNUSED;

use LizardsAndPumpkins\Http\ContentDelivery\PageBuilder\PageSnippets;
use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Http\ContentDelivery\PageBuilder\SnippetTransformation\SnippetTransformation;

/**
 * @todo remove when product listing uses ProductJsonServiceProvider
 */
class PricesJsonSnippetTransformation implements SnippetTransformation
{
    /**
     * @var SnippetTransformation
     */
    private $priceSnippetTransformation;

    public function __construct(SnippetTransformation $priceSnippetTransformation)
    {
        $this->priceSnippetTransformation = $priceSnippetTransformation;
    }

    /**
     * @param string $input
     * @param Context $context
     * @param PageSnippets $pageSnippets
     * @return string
     */
    public function __invoke($input, Context $context, PageSnippets $pageSnippets)
    {
        if (!is_string($input)) {
            return '';
        }

        $allPrices = json_decode($input);
        if (!is_array($allPrices)) {
            return '';
        }

        return json_encode(array_map(function (array $prices) use ($context, $pageSnippets) {
            return array_map(function ($price) use ($context, $pageSnippets) {
                return call_user_func($this->priceSnippetTransformation, $price, $context, $pageSnippets);
            }, $prices);
        }, $allPrices));
    }
}
