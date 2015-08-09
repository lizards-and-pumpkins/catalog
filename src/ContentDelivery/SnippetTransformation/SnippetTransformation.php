<?php


namespace Brera\ContentDelivery\SnippetTransformation;

use Brera\Context\Context;

interface SnippetTransformation
{
    /**
     * @param string $input
     * @param Context $context
     * @return string
     */
    public function __invoke($input, Context $context);
}
