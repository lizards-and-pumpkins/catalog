<?php


namespace LizardsAndPumpkins\ContentDelivery\SnippetTransformation;

use LizardsAndPumpkins\ContentDelivery\PageBuilder\PageSnippets;
use LizardsAndPumpkins\Context\Context;

class SimpleEuroPriceSnippetTransformation implements SnippetTransformation
{
    /**
     * @param string $input
     * @param Context $context
     * @param PageSnippets $pageSnippets
     * @return string
     */
    public function __invoke($input, Context $context, PageSnippets $pageSnippets)
    {
        if (!is_int($input) && !is_string($input)) {
            return '';
        }
        if (is_string($input) && !preg_match('/^-?\d+$/', $input)) {
            return $input;
        }
        return sprintf('%s €', number_format($input / 100, 2, ',', '.'));
    }
}
