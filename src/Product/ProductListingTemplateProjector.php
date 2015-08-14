<?php

namespace Brera\Product;

use Brera\Context\ContextSource;
use Brera\DataPool\DataPoolWriter;
use Brera\InvalidProjectionSourceDataTypeException;
use Brera\Projector;
use Brera\RootSnippetSourceList;
use Brera\SnippetRendererCollection;

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

    public function __construct(SnippetRendererCollection $snippetRendererCollection, DataPoolWriter $dataPoolWriter)
    {
        $this->snippetRendererCollection = $snippetRendererCollection;
        $this->dataPoolWriter = $dataPoolWriter;
    }

    /**
     * @param mixed $projectionSourceData
     * @param ContextSource $context
     * @throws InvalidProjectionSourceDataTypeException
     */
    public function project($projectionSourceData, ContextSource $context)
    {
        if (!($projectionSourceData instanceof RootSnippetSourceList)) {
            throw new InvalidProjectionSourceDataTypeException(
                'First argument must be instance of RootSnippetSourceList.'
            );
        }

        $snippetList = $this->snippetRendererCollection->render($projectionSourceData, $context);
        $this->dataPoolWriter->writeSnippetList($snippetList);
    }
}
