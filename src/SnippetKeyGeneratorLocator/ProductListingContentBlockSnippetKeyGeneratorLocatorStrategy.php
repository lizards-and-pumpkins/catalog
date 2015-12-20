<?php

namespace LizardsAndPumpkins\SnippetKeyGeneratorLocator;

use LizardsAndPumpkins\SnippetKeyGeneratorLocator\Exception\SnippetCodeCanNotBeProcessedException;

class ProductListingContentBlockSnippetKeyGeneratorLocatorStrategy implements SnippetKeyGeneratorLocator
{
    /**
     * @var \Closure
     */
    private $contentBlockKeyGeneratorClosure;

    public function __construct(\Closure $contentBlockKeyGeneratorClosure)
    {
        $this->contentBlockKeyGeneratorClosure = $contentBlockKeyGeneratorClosure;
    }

    /**
     * {@inheritdoc}
     */
    public function canHandle($snippetCode)
    {
        return strpos($snippetCode, 'product_listing_content_block_') === 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getKeyGeneratorForSnippetCode($snippetCode)
    {
        if (!$this->canHandle($snippetCode)) {
            throw new SnippetCodeCanNotBeProcessedException(
                sprintf('%s can not process "%s" snippet code.', __CLASS__, $snippetCode)
            );
        }

        return call_user_func($this->contentBlockKeyGeneratorClosure, $snippetCode);
    }
}
