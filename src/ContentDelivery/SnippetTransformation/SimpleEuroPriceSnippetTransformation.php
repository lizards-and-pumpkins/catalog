<?php


namespace Brera\ContentDelivery\SnippetTransformation;

use Brera\Context\Context;

class SimpleEuroPriceSnippetTransformation implements SnippetTransformation
{
    /**
     * @param string $input
     * @param Context $context
     * @return string
     */
    public function __invoke($input, Context $context)
    {
        if (!is_int($input) && !is_string($input)) {
            return '';
        }
        if (is_string($input) && !preg_match('/^-?\d+$/', $input)) {
            return (string)$input;
        }
        return sprintf('%s €', number_format($input / 100, 2, ',', '.'));
    }
}
