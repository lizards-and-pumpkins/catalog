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
     * @param mixed $projectionData
     * @return Snippet[]
     */
    public function render($projectionData)
    {
        return array_reduce($this->renderers, function (array $carry, SnippetRenderer $renderer) use ($projectionData) {
            return array_merge($carry, $renderer->render($projectionData));
        }, []);
    }
}
