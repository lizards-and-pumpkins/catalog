<?php

namespace LizardsAndPumpkins;

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
     * @return SnippetList
     */
    public function render($projectionSourceData)
    {
        foreach ($this->renderers as $renderer) {
            $this->snippetList->merge($renderer->render($projectionSourceData));
        }

        return $this->snippetList;
    }
}
