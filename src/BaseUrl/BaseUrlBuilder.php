<?php


namespace LizardsAndPumpkins\BaseUrl;

use LizardsAndPumpkins\BaseUrl;
use LizardsAndPumpkins\Context\Context;

interface BaseUrlBuilder
{
    /**
     * @param Context $context
     * @return BaseUrl
     */
    public function create(Context $context);
}
