<?php

namespace LizardsAndPumpkins\ContentDelivery\FacetFieldTransformation;

use LizardsAndPumpkins\Context\Context;

interface FacetFieldTransformation
{
    /**
     * @param string $input
     * @param Context $context
     * @return string
     */
    public function __invoke($input, Context $context);
}
