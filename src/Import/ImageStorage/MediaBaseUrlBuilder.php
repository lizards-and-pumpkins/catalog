<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\ImageStorage;

use LizardsAndPumpkins\Context\Context;

interface MediaBaseUrlBuilder
{
    public function create(Context $context) : string;
}
