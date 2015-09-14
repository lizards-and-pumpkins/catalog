<?php


namespace LizardsAndPumpkins\Context;

use LizardsAndPumpkins\Http\HttpRequest;

class LocaleContextDecorator extends ContextDecorator
{
    const CODE = 'locale';

    private $defaultLocale = 'de_DE';

    private $languageToLocaleMap = [
        'de' => 'de_DE',
        'en' => 'en_US'
    ];

    /**
     * @return string
     */
    protected function getCode()
    {
        return self::CODE;
    }

    /**
     * @return string
     */
    protected function getValueFromContext()
    {
        if ($this->isLocalePresentInSourceData()) {
            return $this->getLocaleFromSourceData();
        }
        if ($this->isRequestPresentInSourceData()) {
            return $this->getLocaleFromRequest();
        }
        throw new UnableToDetermineLocaleException(sprintf(
            'Unable to determine locale from context source data ("%s" and "request" not present)',
            $this->getCode()
        ));
    }

    /**
     * @return string
     */
    private function getLocaleFromRequest()
    {
        $locale = $this->getLocaleFromRequestPath();
        return isset($this->languageToLocaleMap[$locale]) ?
            $this->languageToLocaleMap[$locale] :
            $this->defaultLocale;
    }

    /**
     * @return string
     */
    private function getLocaleFromRequestPath()
    {
        $firstPathPart = $this->getFirstRequestPathPart();
        return (string) substr($firstPathPart, strpos($firstPathPart, '_') + 1);
    }

    /**
     * @return string
     */
    private function getFirstRequestPathPart()
    {
        $path = $this->getRequest()->getUrlPathRelativeToWebFront();
        return '' !== $path ?
            explode('/', $path)[0] :
            '';
    }

    /**
     * @return HttpRequest
     */
    private function getRequest()
    {
        return $this->getSourceData()['request'];
    }

    /**
     * @return bool
     */
    private function isLocalePresentInSourceData()
    {
        return isset($this->getSourceData()['locale']);
    }

    /**
     * @return mixed
     */
    private function getLocaleFromSourceData()
    {
        return $this->getSourceData()['locale'];
    }

    /**
     * @return bool
     */
    private function isRequestPresentInSourceData()
    {
        return isset($this->getSourceData()['request']);
    }
}
