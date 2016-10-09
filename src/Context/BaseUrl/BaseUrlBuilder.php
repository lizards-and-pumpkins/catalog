<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Context\BaseUrl;

use LizardsAndPumpkins\Context\Context;

interface BaseUrlBuilder
{
    public function create(Context $context) : BaseUrl;
}
