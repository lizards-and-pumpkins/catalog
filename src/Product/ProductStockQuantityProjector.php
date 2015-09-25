<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\DataPool\DataPoolWriter;
use LizardsAndPumpkins\InvalidProjectionSourceDataTypeException;
use LizardsAndPumpkins\Projector;
use LizardsAndPumpkins\SnippetRendererCollection;

class ProductStockQuantityProjector implements Projector
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
        if (!($projectionSourceData instanceof ProductStockQuantitySource)) {
            throw new InvalidProjectionSourceDataTypeException(
                'First argument must be a ProductStockQuantitySource instance.'
            );
        }

        $snippetList = $this->snippetRendererCollection->render($projectionSourceData);
        $this->dataPoolWriter->writeSnippetList($snippetList);
    }
}
