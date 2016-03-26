<?php

namespace LizardsAndPumpkins\Import\ContentBlock;

use LizardsAndPumpkins\Context\ContextBuilder;
use LizardsAndPumpkins\DataPool\KeyValueStore\Snippet;
use LizardsAndPumpkins\DataPool\KeyGenerator\SnippetKeyGeneratorLocator;
use LizardsAndPumpkins\Import\SnippetRenderer;

class ContentBlockSnippetRenderer implements SnippetRenderer
{
    /**
     * @var SnippetKeyGeneratorLocator
     */
    private $snippetKeyGeneratorLocator;

    /**
     * @var ContextBuilder
     */
    private $contextBuilder;

    public function __construct(
        SnippetKeyGeneratorLocator $snippetKeyGeneratorLocator,
        ContextBuilder $contextBuilder
    ) {
        $this->snippetKeyGeneratorLocator = $snippetKeyGeneratorLocator;
        $this->contextBuilder = $contextBuilder;
    }

    /**
     * @param ContentBlockSource $contentBlockSource
     * @return Snippet[]
     */
    public function render(ContentBlockSource $contentBlockSource)
    {
        $snippetCode = (string) $contentBlockSource->getContentBlockId();
        $keyGenerator = $this->snippetKeyGeneratorLocator->getKeyGeneratorForSnippetCode($snippetCode);

        $context = $this->contextBuilder->createContext($contentBlockSource->getContextData());
        $keyGeneratorParameters = $contentBlockSource->getKeyGeneratorParams();

        $key = $keyGenerator->getKeyForContext($context, $keyGeneratorParameters);
        $content = $contentBlockSource->getContent();

        return [
            Snippet::create($key, $content)
        ];
    }
}
