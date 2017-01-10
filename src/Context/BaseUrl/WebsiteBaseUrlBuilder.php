<?php

declare(strict_types=1);

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

    public function create(Context $context) : BaseUrl
    {
        if ($this->configReader->has($this->getBaseUrlConfigKey($context))) {
            $baseUrlString = $this->configReader->get($this->getBaseUrlConfigKey($context));
            return new HttpBaseUrl($baseUrlString);
        }

        throw $this->createConfigMissingException($context);
    }

    private function getBaseUrlConfigKey(Context $context) : string
    {
        return self::CONFIG_PREFIX . $this->getWebsiteCode($context);
    }

    private function getWebsiteCode(Context $context) : string
    {
        return $context->getValue(Website::CONTEXT_CODE);
    }

    private function createConfigMissingException(Context $context) : NoConfiguredBaseUrlException
    {
        $message = sprintf('No base URL configuration found for the website "%s"', $this->getWebsiteCode($context));
        return new NoConfiguredBaseUrlException($message);
    }
}
