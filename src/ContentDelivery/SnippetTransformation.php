<?php


namespace Brera\ContentDelivery;

use Brera\Context\Context;

interface SnippetTransformation
{
    /**
     * @param string $content
     * @param Context $context
     * @return string
     */
    public function __invoke($content, Context $context);
}
