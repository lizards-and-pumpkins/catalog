<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductListing\Import;

use LizardsAndPumpkins\DataPool\DataPoolWriter;
use LizardsAndPumpkins\Import\Projector;
use LizardsAndPumpkins\Import\SnippetRendererCollection;

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
     * @param mixed $projectionData
     */
    public function project($projectionData)
    {
        $snippets = $this->snippetRendererCollection->render($projectionData);
        $this->dataPoolWriter->writeSnippets(...$snippets);
    }
}
