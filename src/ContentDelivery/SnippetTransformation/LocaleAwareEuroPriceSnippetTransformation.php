<?php

namespace LizardsAndPumpkins\ContentDelivery\SnippetTransformation;

use LizardsAndPumpkins\ContentDelivery\PageBuilder\PageSnippets;
use LizardsAndPumpkins\ContentDelivery\SnippetTransformation\Exception\NoValidLocaleInContextException;
use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\ContextBuilder\ContextLocale;
use SebastianBergmann\Money\EUR;
use SebastianBergmann\Money\IntlFormatter;

/**
 * @todo remove when the product detail page uses product json only
 */
class LocaleAwareEuroPriceSnippetTransformation implements SnippetTransformation
{
    /**
     * @param string $input
     * @param Context $context
     * @param PageSnippets $pageSnippets
     * @return string
     */
    public function __invoke($input, Context $context, PageSnippets $pageSnippets)
    {
        if (! is_int($input) && ! is_string($input)) {
            return '';
        }
        if (is_string($input) && !preg_match('/^-?\d+$/', $input)) {
            return $input;
        }
        $locale = $this->getLocaleString($context);
        return (new IntlFormatter($locale))->format(new EUR((int) $input));
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
