<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Context\BaseUrl;

use LizardsAndPumpkins\Context\Context;

class IntegrationTestFixedBaseUrlBuilder implements BaseUrlBuilder
{
    public function create(Context $context) : BaseUrl
    {
        return new HttpBaseUrl('http://example.com/');
    }
}
