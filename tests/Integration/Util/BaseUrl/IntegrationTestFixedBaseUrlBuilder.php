<?php

namespace LizardsAndPumpkins\Context\BaseUrl\BaseUrl;

use LizardsAndPumpkins\Context\BaseUrl\BaseUrl;
use LizardsAndPumpkins\Context\BaseUrl\BaseUrlBuilder;
use LizardsAndPumpkins\Context\BaseUrl\HttpBaseUrl;
use LizardsAndPumpkins\Context\Context;

class IntegrationTestFixedBaseUrlBuilder implements BaseUrlBuilder
{
    /**
     * @param Context $context
     * @return BaseUrl
     */
    public function create(Context $context)
    {
        return HttpBaseUrl::fromString('http://example.com/');
    }
}
