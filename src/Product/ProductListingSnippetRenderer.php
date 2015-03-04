<?php

namespace Brera\Product;

use Brera\Context\ContextSource;
use Brera\ProjectionSourceData;
use Brera\Renderer\BlockRenderer;
use Brera\SnippetKeyGenerator;
use Brera\SnippetRenderer;
use Brera\SnippetResult;
use Brera\SnippetResultList;

class ProductListingSnippetRenderer implements SnippetRenderer
{
    /**
     * @var SnippetResultList
     */
    private $snippetResultList;

    /**
     * @var SnippetKeyGenerator
     */
    private $snippetKeyGenerator;

    /**
     * @var BlockRenderer
     */
    private $blockRenderer;

    /**
     * @param SnippetResultList $snippetResultList
     * @param SnippetKeyGenerator $snippetKeyGenerator
     * @param BlockRenderer $blockRenderer
     */
    public function __construct(
        SnippetResultList $snippetResultList, SnippetKeyGenerator $snippetKeyGenerator, BlockRenderer $blockRenderer
    )
    {
        $this->snippetResultList = $snippetResultList;
        $this->snippetKeyGenerator = $snippetKeyGenerator;
        $this->blockRenderer = $blockRenderer;
    }

    /**
     * @param ProjectionSourceData $dataObject
     * @param ContextSource $contextSource
     * @return SnippetResultList
     */
    public function render(ProjectionSourceData $dataObject, ContextSource $contextSource)
    {
        foreach ($contextSource->getAllAvailableContexts() as $context) {
            $content = $this->blockRenderer->render($dataObject, $context);
            /* TODO: Put list related identifier (e.g. num products per page) */
            $key = $this->snippetKeyGenerator->getKeyForContext('product_listing', $context);
            $contentSnippet = SnippetResult::create($key, $content);
            $this->snippetResultList->add($contentSnippet);
        }

        return $this->snippetResultList;
    }
}
