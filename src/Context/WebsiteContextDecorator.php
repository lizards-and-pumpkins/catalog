<?php


namespace Brera\Context;

use Brera\Http\HttpRequest;

class WebsiteContextDecorator extends ContextDecorator
{
    const CODE = 'website';

    private $defaultWebsite = 'ru';

    private $validWebsites = [
        'ru',
        'cy'
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
        if ($this->isWebsiteCodeInSourceData()) {
            return $this->getWebsiteValueFromSourceData();
        }
        if ($this->isRequestInSourceData()) {
            return $this->getWebsiteFromRequest();
        }
        throw new UnableToDetermineWebsiteContextException(sprintf(
            'Unable to determine website from context source data ("%s" and "request" not present)',
            self::CODE
        ));
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
    private function isWebsiteCodeInSourceData()
    {
        return isset($this->getSourceData()[self::CODE]);
    }

    /**
     * @return bool
     */
    private function isRequestInSourceData()
    {
        return isset($this->getSourceData()['request']);
    }

    /**
     * @return string
     */
    private function getWebsiteValueFromSourceData()
    {
        return $this->getSourceData()[self::CODE];
    }

    /**
     * @return string
     */
    private function getWebsiteFromRequest()
    {
        $websiteFromPath = $this->getWebsiteFromRequestPath();
        return in_array($websiteFromPath, $this->validWebsites) ?
            $websiteFromPath :
            $this->defaultWebsite;
    }

    /**
     * @return string
     */
    private function getWebsiteFromRequestPath()
    {
        $firstPathPart = $this->getFirstRequestPathPart();
        $pos = strpos($firstPathPart, '_');
        return $pos > 1 ?
            substr($firstPathPart, 0, $pos) :
            '';
    }

    /**
     * @return string
     */
    private function getFirstRequestPathPart()
    {
        $path = $this->getRequest()->getUrlPathRelativeToWebFront();
        return '' !== $path ?
            explode('/', $path, 2)[0] :
            '';
    }
}
