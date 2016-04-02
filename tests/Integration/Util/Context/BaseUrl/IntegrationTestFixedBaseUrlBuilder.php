<?php

namespace LizardsAndPumpkins\Context\BaseUrl;

use LizardsAndPumpkins\Context\BaseUrl\BaseUrl;
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
