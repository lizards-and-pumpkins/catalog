<?php

namespace Brera;

use Brera\Context\ContextSource;

class SnippetRendererCollection
{
    /**
     * @var SnippetList
     */
    private $snippetList;

    /**
     * @var SnippetRenderer[]
     */
    private $renderers = [];

    /**
     * @param SnippetRenderer[] $renderers
     * @param SnippetList $snippetList
     */
    public function __construct(array $renderers, SnippetList $snippetList)
    {
        $this->renderers = $renderers;
        $this->snippetList = $snippetList;
    }
    
    /**
     * @param mixed $projectionSourceData
     * @param ContextSource $contextSource
     * @return SnippetList
     */
    public function render($projectionSourceData, ContextSource $contextSource)
    {
        foreach ($this->renderers as $renderer) {
            $this->snippetList->merge($renderer->render($projectionSourceData, $contextSource));
        }

        return $this->snippetList;
    }
}
