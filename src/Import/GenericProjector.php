<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import;

use LizardsAndPumpkins\DataPool\DataPoolWriter;
use LizardsAndPumpkins\DataPool\KeyValueStore\Snippet;

class GenericProjector implements Projector
{
    /**
     * @var DataPoolWriter
     */
    private $dataPoolWriter;

    /**
     * @var SnippetRenderer[]
     */
    private $snippetRenderers;

    public function __construct(DataPoolWriter $dataPoolWriter, SnippetRenderer ...$snippetRenderers)
    {
        $this->dataPoolWriter = $dataPoolWriter;
        $this->snippetRenderers = $snippetRenderers;
    }

    /**
     * @param mixed $projectionData
     */
    public function project($projectionData)
    {
        $snippets = $this->getSnippets($projectionData);
        $this->dataPoolWriter->writeSnippets(...$snippets);
    }

    /**
     * @param mixed $projectionData
     * @return Snippet[]
     */
    private function getSnippets($projectionData): array
    {
        return array_reduce(
            $this->snippetRenderers,
            function ($carry, SnippetRenderer $snippetRenderer) use ($projectionData) {
                return array_merge($carry, $snippetRenderer->render($projectionData));
            },
            []
        );
    }
}
