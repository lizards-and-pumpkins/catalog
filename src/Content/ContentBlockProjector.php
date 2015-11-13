<?php

namespace LizardsAndPumpkins\Content;

use LizardsAndPumpkins\DataPool\DataPoolWriter;
use LizardsAndPumpkins\Exception\InvalidProjectionSourceDataTypeException;
use LizardsAndPumpkins\Projector;
use LizardsAndPumpkins\SnippetRendererCollection;

class ContentBlockProjector implements Projector
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
     * @param mixed $projectionSourceData
     */
    public function project($projectionSourceData)
    {
        if (!($projectionSourceData instanceof ContentBlockSource)) {
            throw new InvalidProjectionSourceDataTypeException(
                'First argument must be instance of ContentBlockSource.'
            );
        }

        $snippetList = $this->snippetRendererCollection->render($projectionSourceData);
        $this->dataPoolWriter->writeSnippetList($snippetList);
    }
}
