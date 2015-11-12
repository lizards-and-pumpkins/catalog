<?php

namespace LizardsAndPumpkins\Content;

use LizardsAndPumpkins\ContentBlockSnippetKeyGeneratorLocatorStrategy;
use LizardsAndPumpkins\Context\ContextBuilder;
use LizardsAndPumpkins\Snippet;
use LizardsAndPumpkins\SnippetList;
use LizardsAndPumpkins\SnippetRenderer;

class ContentBlockSnippetRenderer implements SnippetRenderer
{
    const CODE = 'content_block';

    /**
     * @var SnippetList
     */
    private $snippetList;

    /**
     * @var ContentBlockSnippetKeyGeneratorLocatorStrategy
     */
    private $snippetKeyGeneratorLocator;

    /**
     * @var ContextBuilder
     */
    private $contextBuilder;

    public function __construct(
        SnippetList $snippetList,
        ContentBlockSnippetKeyGeneratorLocatorStrategy $snippetKeyGeneratorLocator,
        ContextBuilder $contextBuilder
    ) {
        $this->snippetList = $snippetList;
        $this->snippetKeyGeneratorLocator = $snippetKeyGeneratorLocator;
        $this->contextBuilder = $contextBuilder;
    }

    /**
     * @param ContentBlockSource $contentBlockSource
     * @return SnippetList
     */
    public function render(ContentBlockSource $contentBlockSource)
    {
        $snippetCode = (string) $contentBlockSource->getContentBlockId();
        $keyGenerator = $this->snippetKeyGeneratorLocator->getKeyGeneratorForSnippetCode($snippetCode);

        $context = $this->contextBuilder->createContext($contentBlockSource->getContextData());
        $keyGeneratorParameters = [];

        $key = $keyGenerator->getKeyForContext($context, $keyGeneratorParameters);
        $content = $contentBlockSource->getContent();
        $snippet = Snippet::create($key, $content);
        $this->snippetList->add($snippet);

        return $this->snippetList;
    }
}
