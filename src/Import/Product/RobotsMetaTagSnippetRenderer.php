<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Product;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\DataPool\KeyValueStore\Snippet;
use LizardsAndPumpkins\DataPool\KeyGenerator\SnippetKeyGenerator;
use LizardsAndPumpkins\Import\SnippetRenderer;

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
    public function render(Context $context) : array
    {
        return array_map(function ($tagContent) use ($context) {
            return $this->createRobotsMetaTagSnippetForContent($context, $tagContent);
        }, $this->robotsMetaTags);
    }

    private function createRobotsMetaTagSnippetForContent(Context $context, string $tagContent) : Snippet
    {
        $snippetKey = $this->keyGenerator->getKeyForContext($context, ['robots' => $tagContent]);
        $snippetContent = sprintf('<meta name="robots" content="%s"/>', $tagContent);
        return Snippet::create($snippetKey, $snippetContent);
    }
}
