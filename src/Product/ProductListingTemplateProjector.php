<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\DataPool\DataPoolWriter;
use LizardsAndPumpkins\Projection\Projector;
use LizardsAndPumpkins\SnippetRendererCollection;

class ProductListingTemplateProjector implements Projector
{
    /**
     * @var SnippetRendererCollection
     */
    private $snippetRendererCollection;

    /**
     * @var DataPoolWriter
     */
    private $dataPoolWriter;
    
    public function __construct(
        SnippetRendererCollection $snippetRendererCollection,
        DataPoolWriter $dataPoolWriter
    ) {
        $this->snippetRendererCollection = $snippetRendererCollection;
        $this->dataPoolWriter = $dataPoolWriter;
    }

    /**
     * @param mixed $productsPerPageSourceData
     */
    public function project($productsPerPageSourceData)
    {
        $projectionData = [];
        $snippets = $this->snippetRendererCollection->render($projectionData);
        $this->dataPoolWriter->writeSnippets(...$snippets);
    }
}
