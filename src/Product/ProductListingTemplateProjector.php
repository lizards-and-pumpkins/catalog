<?php

namespace Brera\Product;

use Brera\Context\ContextSource;
use Brera\DataPool\DataPoolWriter;
use Brera\InvalidProjectionDataSourceTypeException;
use Brera\ProjectionSourceData;
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
     * @param ProjectionSourceData $dataObject
     * @param ContextSource $context
     * @throws InvalidProjectionDataSourceTypeException
     */
    public function project(ProjectionSourceData $dataObject, ContextSource $context)
    {
        if (!($dataObject instanceof RootSnippetSourceList)) {
            throw new InvalidProjectionDataSourceTypeException(
                'First argument must be instance of RootSnippetSourceList.'
            );
        }

        $snippetList = $this->snippetRendererCollection->render($dataObject, $context);
        $this->dataPoolWriter->writeSnippetList($snippetList);
    }
}
