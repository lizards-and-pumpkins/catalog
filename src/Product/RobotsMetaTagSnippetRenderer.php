<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Snippet;
use LizardsAndPumpkins\SnippetKeyGenerator;
use LizardsAndPumpkins\SnippetRenderer;

class RobotsMetaTagSnippetRenderer implements SnippetRenderer
{
    /**
     * @var SnippetKeyGenerator
     */
    private $keyGenerator;

    private $robotsMetaTags = ['all', 'noindex'];

    public function __construct(SnippetKeyGenerator $keyGenerator)
    {
        $this->keyGenerator = $keyGenerator;
    }

    /**
     * @param Context $context
     * @return Snippet[]
     */
    public function render(Context $context)
    {
        return array_map(function ($tagContent) use ($context) {
            return $this->createRobotsMetaTagSnippetForContent($context, $tagContent);
        }, $this->robotsMetaTags);
    }

    /**
     * @param Context $context
     * @param string $tagContent
     * @return Snippet
     */
    private function createRobotsMetaTagSnippetForContent(Context $context, $tagContent)
    {
        $snippetKey = $this->keyGenerator->getKeyForContext($context, ['robots' => $tagContent]);
        $snippetContent = sprintf('<meta name="robots" content="%s"/>', $tagContent);
        return Snippet::create($snippetKey, $snippetContent);
    }
}
