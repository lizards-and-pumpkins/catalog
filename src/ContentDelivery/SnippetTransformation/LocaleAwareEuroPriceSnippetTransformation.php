<?php


namespace Brera\ContentDelivery\SnippetTransformation;

use Brera\Context\Context;
use Brera\Context\LanguageContextDecorator;
use SebastianBergmann\Money\EUR;
use SebastianBergmann\Money\IntlFormatter;

class LocaleAwareEuroPriceSnippetTransformation implements SnippetTransformation
{
    /**
     * @param string $input
     * @param Context $context
     * @return string
     */
    public function __invoke($input, Context $context)
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
        $locale = $context->getValue(LanguageContextDecorator::CODE);
        if (is_null($locale)) {
            throw new NoValidLocaleInContext('No valid locale in context');
        }
        return $locale;
    }
}
