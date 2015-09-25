<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Context\ContextSource;
use LizardsAndPumpkins\DataPool\DataPoolWriter;
use LizardsAndPumpkins\Projector;
use LizardsAndPumpkins\SnippetRendererCollection;

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
        $snippetList = $this->snippetRendererCollection->render($projectionSourceData);
        $this->dataPoolWriter->writeSnippetList($snippetList);
    }
}
