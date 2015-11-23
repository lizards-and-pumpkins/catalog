<?php


namespace LizardsAndPumpkins\BaseUrl;

use LizardsAndPumpkins\ConfigReader;
use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\WebsiteContextDecorator;

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
        return HttpBaseUrl::fromString($baseUrlString);
    }

    /**
     * @param Context $context
     * @return string
     */
    private function getBaseUrlConfigKey(Context $context)
    {
        return self::CONFIG_PREFIX . $context->getValue(WebsiteContextDecorator::CODE);
    }
}
