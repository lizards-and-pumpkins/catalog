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
        $this->dataPoolWriter->writeSnippets(...$this->getSnippets($projectionData));
    }

    /**
     * @param mixed $projectionData
     * @return Snippet[]
     */
    private function getSnippets($projectionData): array
    {
        return array_map(function (SnippetRenderer $snippetRenderer) use ($projectionData) {
            return $snippetRenderer->render($projectionData);
        }, $this->snippetRenderers);
    }
}
