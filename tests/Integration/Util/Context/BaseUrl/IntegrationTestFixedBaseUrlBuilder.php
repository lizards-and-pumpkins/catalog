<?php

namespace LizardsAndPumpkins\Context\BaseUrl;

use LizardsAndPumpkins\Context\Context;

class IntegrationTestFixedBaseUrlBuilder implements BaseUrlBuilder
{
    public function create(Context $context) : BaseUrl
    {
        return HttpBaseUrl::fromString('http://example.com/');
    }
}
