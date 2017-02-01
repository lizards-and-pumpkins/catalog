<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\ContentBlock;

use LizardsAndPumpkins\DataPool\KeyValueStore\Snippet;
use LizardsAndPumpkins\DataPool\KeyGenerator\SnippetKeyGeneratorLocator;
use LizardsAndPumpkins\Import\SnippetRenderer;

class ContentBlockSnippetRenderer implements SnippetRenderer
{
    /**
     * @var SnippetKeyGeneratorLocator
     */
    private $snippetKeyGeneratorLocator;
    
    public function __construct(SnippetKeyGeneratorLocator $snippetKeyGeneratorLocator)
    {
        $this->snippetKeyGeneratorLocator = $snippetKeyGeneratorLocator;
    }

    /**
     * @param ContentBlockSource $contentBlockSource
     * @return Snippet[]
     */
    public function render(ContentBlockSource $contentBlockSource) : array
    {
        $snippetCode = (string) $contentBlockSource->getContentBlockId();
        $keyGenerator = $this->snippetKeyGeneratorLocator->getKeyGeneratorForSnippetCode($snippetCode);

        $keyGeneratorParameters = $contentBlockSource->getKeyGeneratorParams();

        $key = $keyGenerator->getKeyForContext($contentBlockSource->getContext(), $keyGeneratorParameters);
        $content = $contentBlockSource->getContent();

        return [Snippet::create($key, $content)];
    }
}
