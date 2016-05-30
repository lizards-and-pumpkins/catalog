<?php

namespace LizardsAndPumpkins\Context\BaseUrl;

use LizardsAndPumpkins\Context\Website\Exception\NoConfiguredBaseUrlException;
use LizardsAndPumpkins\Context\Website\Website;
use LizardsAndPumpkins\Util\Config\ConfigReader;
use LizardsAndPumpkins\Context\Context;

class WebsiteBaseUrlBuilder implements BaseUrlBuilder
{
    const CONFIG_PREFIX = 'base_url_';
    
    /**
     * @var ConfigReader
     */
    private $configReader;

    public function __construct(ConfigReader $configReader)
    {
        $this->configReader = $configReader;
    }

    /**
     * @param Context $context
     * @return HttpBaseUrl
     */
    public function create(Context $context)
    {
        $baseUrlString = $this->configReader->get($this->getBaseUrlConfigKey($context));
        if (! $baseUrlString) {
            throw $this->createConfigMissingException($context);
        }
        return HttpBaseUrl::fromString($baseUrlString);
    }

    /**
     * @param Context $context
     * @return string
     */
    private function getBaseUrlConfigKey(Context $context)
    {
        return self::CONFIG_PREFIX . $this->getWebsiteCode($context);
    }

    /**
     * @param Context $context
     * @return string
     */
    private function getWebsiteCode(Context $context)
    {
        return $context->getValue(Website::CONTEXT_CODE);
    }

    /**
     * @param Context $context
     * @return NoConfiguredBaseUrlException
     */
    private function createConfigMissingException(Context $context)
    {
        $message = sprintf('No base URL configuration found for the website "%s"', $this->getWebsiteCode($context));
        return new NoConfiguredBaseUrlException($message);
    }
}
