<?php


namespace Brera\Context;

use Brera\Http\HttpRequest;

class WebsiteContextDecorator extends ContextDecorator
{
    const CODE = 'website';
    
    private $defaultWebsite = 'ru';
    
    private $validWebsites = [
        'ru'
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
        if ($this->isWebsiteCodeinSourceData()) {
            return $this->getWebsiteValueFromSourceData();
        }
        if ($this->isRequestInSourceData()) {
            return $this->getWebsiteFromRequest();
        }
        $this->throwUnableToDetermineWebsiteException();
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
    private function isWebsiteCodeinSourceData()
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
        return in_array($websiteFromPath, $this->validWebsites)?
            $websiteFromPath :
            $this->defaultWebsite;
    }

    /**
     * @return string
     */
    private function getWebsiteFromRequestPath()
    {
        $path = $this->getRequest()->getUrl()->getPathRelativeToWebFront();
        $parts = explode('/', $path);
        return count($parts) > 0 ?
            $parts[0] :
            '';
    }

    /**
     * @return void
     */
    private function throwUnableToDetermineWebsiteException()
    {
        throw new UnableToDetermineWebsiteContextException(sprintf(
            'Unable to determine website from context source data ("%s" and "request" not present)',
            self::CODE
        ));
    }
}
