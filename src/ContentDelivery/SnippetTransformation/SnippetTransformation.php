<?php


namespace LizardsAndPumpkins\ContentDelivery\SnippetTransformation;

use LizardsAndPumpkins\Context\Context;

interface SnippetTransformation
{
    /**
     * @param string $input
     * @param Context $context
     * @return string
     */
    public function __invoke($input, Context $context);
}
