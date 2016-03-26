<?php

namespace LizardsAndPumpkins\Import\ImageStorage;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Http\HttpUrl;

interface MediaBaseUrlBuilder
{
    /**
     * @param Context $context
     * @return HttpUrl
     */
    public function create(Context $context);
}
