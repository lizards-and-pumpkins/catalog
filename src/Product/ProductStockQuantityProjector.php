<?php

namespace Brera\Product;

use Brera\Context\ContextSource;
use Brera\DataPool\DataPoolWriter;
use Brera\InvalidProjectionSourceDataTypeException;
use Brera\Projector;
use Brera\SnippetRendererCollection;

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
     * @param ContextSource $contextSource
     * @throws InvalidProjectionSourceDataTypeException
     */
    public function project($projectionSourceData, ContextSource $contextSource)
    {
        if (!($projectionSourceData instanceof ProductStockQuantitySource)) {
            throw new InvalidProjectionSourceDataTypeException('First argument must be instance of ProductSource.');
        }

        $snippetList = $this->snippetRendererCollection->render($projectionSourceData, $contextSource);
        $this->dataPoolWriter->writeSnippetList($snippetList);
    }
}
