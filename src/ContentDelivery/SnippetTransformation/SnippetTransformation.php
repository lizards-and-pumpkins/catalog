<?php


namespace LizardsAndPumpkins\ContentDelivery\SnippetTransformation;

use LizardsAndPumpkins\ContentDelivery\PageBuilder\PageSnippets;
use LizardsAndPumpkins\Context\Context;

interface SnippetTransformation
{
    /**
     * @param string $input
     * @param Context $context
     * @param PageSnippets $pageSnippets
     * @return string
     */
    public function __invoke($input, Context $context, PageSnippets $pageSnippets);
}
