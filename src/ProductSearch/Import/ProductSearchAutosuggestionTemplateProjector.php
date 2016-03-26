<?php

namespace LizardsAndPumpkins\ProductSearch\Import;

use LizardsAndPumpkins\DataPool\DataPoolWriter;
use LizardsAndPumpkins\Import\Projector;
use LizardsAndPumpkins\Import\SnippetRendererCollection;

class ProductSearchAutosuggestionTemplateProjector implements Projector
{
    /**
     * @var DataPoolWriter
     */
    private $dataPoolWriter;

    /**
     * @var SnippetRendererCollection
     */
    private $snippetRendererCollection;

    public function __construct(DataPoolWriter $dataPoolWriter, SnippetRendererCollection $snippetRendererCollection)
    {
        $this->dataPoolWriter = $dataPoolWriter;
        $this->snippetRendererCollection = $snippetRendererCollection;
    }

    /**
     * @param mixed $projectionSourceData
     */
    public function project($projectionSourceData)
    {
        $snippets = $this->snippetRendererCollection->render($projectionSourceData);
        $this->dataPoolWriter->writeSnippets(...$snippets);
    }
}
