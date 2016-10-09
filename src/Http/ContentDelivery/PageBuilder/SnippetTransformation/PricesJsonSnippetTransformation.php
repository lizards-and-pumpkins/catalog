<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Http\ContentDelivery\PageBuilder\SnippetTransformation;

use LizardsAndPumpkins\Http\ContentDelivery\PageBuilder\PageSnippets;
use LizardsAndPumpkins\Context\Context;

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
     * @param mixed $input
     * @param Context $context
     * @param PageSnippets $pageSnippets
     * @return string
     */
    public function __invoke($input, Context $context, PageSnippets $pageSnippets) : string
    {
        $allPrices = json_decode((string) $input);
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
