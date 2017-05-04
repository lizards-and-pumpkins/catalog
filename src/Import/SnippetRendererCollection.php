<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import;

use LizardsAndPumpkins\DataPool\KeyValueStore\Snippet;

class SnippetRendererCollection
{
    /**
     * @var SnippetRenderer[]
     */
    private $renderers = [];

    public function __construct(SnippetRenderer ...$renderers)
    {
        $this->renderers = $renderers;
    }
    
    /**
     * @param mixed $projectionData
     * @return Snippet[]
     */
    public function render($projectionData) : array
    {
        return array_reduce($this->renderers, function (array $carry, SnippetRenderer $renderer) use ($projectionData) {
            return array_merge($carry, $renderer->render($projectionData));
        }, []);
    }
}
