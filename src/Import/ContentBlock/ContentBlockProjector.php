<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\ContentBlock;

use LizardsAndPumpkins\DataPool\DataPoolWriter;
use LizardsAndPumpkins\DataPool\KeyValueStore\Snippet;
use LizardsAndPumpkins\Import\Projector;
use LizardsAndPumpkins\Import\SnippetRenderer;

class ContentBlockProjector implements Projector
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
     * @param mixed $projectionSourceData
     */
    public function project($projectionSourceData)
    {
        $this->dataPoolWriter->writeSnippets(...$this->getSnippets($projectionSourceData));
    }

    /**
     * @param ContentBlockSource $projectionData
     * @return Snippet[]
     */
    private function getSnippets(ContentBlockSource $projectionData): array
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
