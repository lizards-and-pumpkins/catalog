<?php

namespace LizardsAndPumpkins\Context\Locale;

use LizardsAndPumpkins\Context\ContextBuilder;
use LizardsAndPumpkins\Context\ContextPartBuilder;
use LizardsAndPumpkins\Http\HttpRequest;

class ContextLocale implements ContextPartBuilder
{
    private $default = 'fr_FR';

    /*
     * TODO: The mapping array must be moved to configuration or a factory or a dedicated
     * TODO: class or the configuration once the business rules have become more stable.
     */
    private $websiteCodeToLocaleMap = [
        'ru_de' => 'de_DE',
        'ru_en' => 'en_US',
        'ru_es' => 'es_ES',
        'fr' => 'fr_FR'
    ];

    /**
     * @return string
     */
    public function getCode()
    {
        return Locale::CONTEXT_CODE;
    }

    /**
     * @param mixed[] $inputDataSet
     * @return string
     */
    public function getValue(array $inputDataSet)
    {
        if (isset($inputDataSet[Locale::CONTEXT_CODE])) {
            return (string) $inputDataSet[Locale::CONTEXT_CODE];
        }

        if (isset($inputDataSet[ContextBuilder::REQUEST])) {
            return $this->getLocaleFromRequest($inputDataSet[ContextBuilder::REQUEST]);
        }

        return $this->default;
    }

    /**
     * @param HttpRequest $request
     * @return string
     */
    private function getLocaleFromRequest(HttpRequest $request)
    {
        $websiteCodeInUrl = $this->getFirstRequestPathPart($request);

        if (isset($this->websiteCodeToLocaleMap[$websiteCodeInUrl])) {
            return $this->websiteCodeToLocaleMap[$websiteCodeInUrl];
        }

        return $this->default;
    }

    /**
     * @param HttpRequest $request
     * @return string
     */
    private function getFirstRequestPathPart(HttpRequest $request)
    {
        $pathTokens = explode('/', $request->getPathWithWebsitePrefix());
        return $pathTokens[0];
    }
}
