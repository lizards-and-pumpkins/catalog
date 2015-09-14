<?php

namespace LizardsAndPumpkins\Content;

use LizardsAndPumpkins\Context\ContextBuilder;
use LizardsAndPumpkins\Snippet;
use LizardsAndPumpkins\SnippetKeyGenerator;
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
     * @var SnippetKeyGenerator
     */
    private $snippetKeyGenerator;

    /**
     * @var ContextBuilder
     */
    private $contextBuilder;

    public function __construct(
        SnippetList $snippetList,
        SnippetKeyGenerator $snippetKeyGenerator,
        ContextBuilder $contextBuilder
    ) {
        $this->snippetList = $snippetList;
        $this->snippetKeyGenerator = $snippetKeyGenerator;
        $this->contextBuilder = $contextBuilder;
    }

    /**
     * @param ContentBlockSource $contentBlockSource
     * @return SnippetList
     */
    public function render(ContentBlockSource $contentBlockSource)
    {
        $context = $this->contextBuilder->createContext($contentBlockSource->getContextData());
        $key = $this->snippetKeyGenerator->getKeyForContext($context, [
            'content_block_id' => (string) $contentBlockSource->getContentBlockId()
        ]);
        $content = $contentBlockSource->getContent();
        $snippet = Snippet::create($key, $content);
        $this->snippetList->add($snippet);

        return $this->snippetList;
    }
}
