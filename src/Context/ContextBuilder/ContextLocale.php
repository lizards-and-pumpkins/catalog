<?php


namespace LizardsAndPumpkins\Context\ContextBuilder;

use LizardsAndPumpkins\Context\ContextBuilder;
use LizardsAndPumpkins\Http\HttpRequest;

class ContextLocale implements ContextPartBuilder
{
    const CODE = 'locale';

    private $default = 'de_DE';

    /*
     * TODO: The mapping array can be moved to configuration or a factory or a dedicated
     * TODO: class or the configuration once the business rules have become more stable.
     */
    private $languageToLocaleMap = [
        'de' => 'de_DE',
        'en' => 'en_US',
        'fr' => 'fr_FR'
    ];

    /**
     * @return string
     */
    public function getCode()
    {
        return self::CODE;
    }

    /**
     * @param mixed[] $inputDataSet
     * @return string
     */
    public function getValue(array $inputDataSet)
    {
        if (isset($inputDataSet[self::CODE])) {
            return (string) $inputDataSet[self::CODE];
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
        $language = $this->getLanguageFromRequest($request);
        return isset($this->languageToLocaleMap[$language]) ?
            $this->languageToLocaleMap[$language] :
            $this->default;
    }

    /**
     * @param HttpRequest $request
     * @return string
     */
    private function getLanguageFromRequest(HttpRequest $request)
    {
        $firstPathPart = $this->getFirstRequestPathPart($request);
        return (string) substr($firstPathPart, 0, 2);
    }

    /**
     * @param HttpRequest $request
     * @return string
     */
    private function getFirstRequestPathPart(HttpRequest $request)
    {
        $path = ltrim($request->getUrlPathRelativeToWebFront(), '/');
        return '' !== $path ?
            explode('/', $path)[0] :
            '';
    }
}
