<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import;

use LizardsAndPumpkins\DataPool\DataPoolWriter;

class GenericProjector implements Projector
{
    /**
     * @var SnippetRendererCollection
     */
    private $snippetRendererCollection;

    /**
     * @var DataPoolWriter
     */
    private $dataPoolWriter;
    
    public function __construct(SnippetRendererCollection $snippetRendererCollection, DataPoolWriter $dataPoolWriter)
    {
        $this->snippetRendererCollection = $snippetRendererCollection;
        $this->dataPoolWriter = $dataPoolWriter;
    }

    /**
     * @param mixed $projectionData
     */
    public function project($projectionData)
    {
        $snippets = $this->snippetRendererCollection->render($projectionData);
        $this->dataPoolWriter->writeSnippets(...$snippets);
    }
}
