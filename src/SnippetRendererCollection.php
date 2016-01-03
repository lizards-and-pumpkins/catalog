<?php

namespace LizardsAndPumpkins;

class SnippetRendererCollection
{
    /**
     * @var SnippetRenderer[]
     */
    private $renderers = [];

    /**
     * @param SnippetRenderer[] $renderers
     */
    public function __construct(array $renderers)
    {
        $this->renderers = $renderers;
    }
    
    /**
     * @param mixed $projectionSourceData
     * @return SnippetList
     */
    public function render($projectionSourceData)
    {
        $snippetList = new SnippetList();

        foreach ($this->renderers as $renderer) {
            $snippetList->merge($renderer->render($projectionSourceData));
        }

        return $snippetList;
    }
}
